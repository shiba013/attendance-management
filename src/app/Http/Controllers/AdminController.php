<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use App\Models\WorkRequest;
use App\Models\WorkRequestTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $thisDate = $request->input('date')
        ? Carbon::createFromFormat('Y-m-d', $request->input('date'))->startOfDay()
        : now()->startOfDay();

        $previousDay = $thisDate->copy()->subDay()->format('Y-m-d');
        $nextDay = $thisDate->copy()->addDay()->format('Y-m-d');

        $works = Work::with('user', 'rests')
        ->whereDate('date', $thisDate)
        ->orderBy('date', 'asc')->get();
        return view('admin.attendance_list', compact('thisDate', 'previousDay', 'nextDay', 'works'));
    }

    public function staff()
    {
        $users = User::all();
        return view('admin.staff_list', compact('users'));
    }

    public function private(Request $request, $userId)
    {
        $user = User::with('works')
        ->where('id', $userId)
        ->first();

        $inputMonth = $request->input('date');
        $thisMonth = $inputMonth
        ? Carbon::parse($inputMonth)->startOfMonth()
        : now()->startOfMonth();

        $previousMonth = $thisMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $thisMonth->copy()->addMonth()->format('Y-m');

        $works = Work::with('rests')
        ->whereBetween('date', [
            $thisMonth->copy()->startOfMonth(),
            $thisMonth->copy()->endOfMonth(),
        ])
        ->orderBy('date', 'asc')->get();
        return view('admin.private_list', compact('user', 'thisMonth', 'previousMonth', 'nextMonth', 'works'));
    }

    public function adminRequestList(Request $request)
    {
        $query = WorkRequest::with('user', 'work', 'times');
        $tab = $request->query('tab');
        if ($tab == 'done') {
            $query->where('status', 1);
        } elseif($tab == '') {
            $query->where('status', 0);
        }
        $corrections = $query->get();
        return view('request_list', compact('corrections', 'tab'));
    }

    public function approve($workRequestId)
    {
        $workRequest = WorkRequest::with(['user', 'work.rests', 'times'])
        ->find($workRequestId);
        $work = $workRequest ? $workRequest->work : null;
        $times = $workRequest
        ? WorkRequestTime::where('work_request_id', $workRequest->id)->get()
        : collect();

        $startWorkAfter = optional($times->firstWhere('status', 1))->after_time;
        $endWorkAfter   = optional($times->firstWhere('status', 2))->after_time;

        $startWork = $startWorkAfter
        ? $startWorkAfter->format('H:i')
        : ($work->start_time ? Carbon::parse($work->start_time)->format('H:i') : '');

        $endWork = $endWorkAfter
        ? $endWorkAfter->format('H:i')
        : ($work->end_time ? Carbon::parse($work->end_time)->format('H:i') : '');

        $restRequestTimes = [];
        $startRestNew = '';
        $endRestNew = '';

        if($workRequest) {
            $restRequests = $times->whereIn('status', [3, 4])->groupBy('rest_id');

            foreach ($work->rests as $rest) {
                $restGroup = $restRequests[$rest->id] ?? collect();
                $startRestAfter = optional($restGroup->firstWhere('status', 3))->after_time ?? null;
                $endRestAfter = optional($restGroup->firstWhere('status', 4))->after_time ?? null;

                $restRequestTimes[$rest->id] = [
                    'start_rest' => $startRestAfter
                    ? $startRestAfter->format('H:i')
                    : (isset($rest->start_time) ? Carbon::parse($rest->start_time)->format('H:i') : ''),
                    'end_rest' => $endRestAfter
                    ? $endRestAfter->format('H:i')
                    : (isset($rest->end_time) ? Carbon::parse($rest->end_time)->format('H:i') : ''),
                ];
            }

            $newStartRest = optional($times->where('rest_id', null)->firstWhere('status', 3))->after_time;
            $newEndRest   = optional($times->where('rest_id', null)->firstWhere('status', 4))->after_time;

            $restRequestTimes['new'] = [
                'start_rest' => $newStartRest ? $newStartRest->format('H:i') : '',
                'end_rest'   => $newEndRest ? $newEndRest->format('H:i') : '',
            ];
            $startRestNew = $restRequestTimes['new']['start_rest'] ?? '';
            $endRestNew = $restRequestTimes['new']['end_rest'] ?? '';
        }

        return view('admin.approval', compact('work', 'workRequest', 'startWork', 'endWork', 'restRequestTimes', 'startRestNew', 'endRestNew'));
    }

    public function update(UpdateRequest $request, $workRequestId)
    {
        $workRequest = WorkRequest::with(['user', 'work.rests', 'times'])
        ->find($workRequestId);
        $work = $workRequest->work;

        DB::transaction(function () use ($request, $workRequest) {
            $workRequest->update([
                'status' => true,
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            foreach (collect($workRequest->times) as $time) {
                if ($time->status === 1) {
                    $work = $workRequest->work;

                    if ($work) {
                        if ($time->type === 1) {
                            $work->update([
                                'start_time' => $time->after_time,
                            ]);
                        } elseif ($time->type === 2) {
                            $work->update([
                                'end_time' => $time->after_time,
                            ]);
                        }
                    }
                }
                if ($time->rest_id && in_array($time->status, [3, 4])) {
                    $rest = $time->rest;
                    $rest->update([
                        $time->status === 3 ? 'start_time' : 'end_time' => $time->after_time,
                    ]);
                }
            }
            $startRestNew = optional($workRequest->times->whereNull('rest_id')
            ->firstWhere('status', 3))->after_time;
            $endRestNew = optional($workRequest->times->whereNull('rest_id')
            ->firstWhere('status', 4))->after_time;

            if ($startRestNew || $endRestNew) {
                $newRest = new Rest([
                    'user_id'    => $workRequest->user_id,
                    'start_time' => $startRestNew,
                    'end_time'   => $endRestNew,
                ]);
                $work->rests()->save($newRest);
            }
        });
        return redirect("/stamp_correction_request/approve/{$workRequestId}");
    }
}
