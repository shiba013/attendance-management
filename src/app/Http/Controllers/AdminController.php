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
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $dates = collect(CarbonPeriod::create(
            $thisMonth->copy()->startOfMonth(), $thisMonth->copy()->endOfMonth()
        ));

        $works = Work::with('rests')
        ->whereBetween('date', [
            $thisMonth->copy()->startOfMonth(),
            $thisMonth->copy()->endOfMonth(),
        ])
        ->orderBy('date', 'asc')->get();

        $dailyWorks = $dates->map(function ($date) use ($works) {
            $work = $works->first(function ($w) use ($date) {
                return Carbon::parse($w->date)->isSameDay($date);
            });
            return [
                'date' => $date,
                'work' => $work,
                'id' => optional($work)->id,
            ];
        });
        return view('admin.private_list', compact('user', 'thisMonth', 'previousMonth', 'nextMonth', 'works', 'dailyWorks'));
    }

    public function adminRequestList(Request $request)
    {
        $query = WorkRequest::with('user', 'work', 'times')
        ->whereHas('user', function ($q) {
            $q->where('role', 0);
        });
        $tab = $request->query('tab');
        if($tab == 'done') {
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

            foreach($work->rests as $rest) {
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
            $newStartRest = optional($times->firstWhere('status', 5))->after_time;
            $newEndRest   = optional($times->firstWhere('status', 6))->after_time;

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
        $workDate = Carbon::parse($work->date)->format('Y-m-d');

        DB::transaction(function () use ($request, $workRequest ,$work, $workDate) {
            $workRequest->update([
                'status' => 1,
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => Carbon::now(),
            ]);

            foreach(collect($workRequest->times) as $time) {
                $afterTime = Carbon::parse($workDate)->setTimeFromTimeString($time->after_time);
                if($time->status === 1 && $work) {
                    $work->update([
                        'start_time' => Carbon::parse($time->after_time),
                    ]);
                }
                if($time->status === 2 && $work) {
                    $work->update([
                        'end_time' => Carbon::parse($time->after_time),
                    ]);
                }
                if($time->rest_id && in_array($time->status, [3, 4])) {
                    $rest = $time->rest;
                    $beforeTimeRaw = $time->status === 3 ? $rest->start_time : $rest->end_time;
                    $beforeTime = Carbon::parse($beforeTimeRaw)->format('H:i');
                    $afterTimeStr = $time->after_time;

                    if($afterTimeStr && $beforeTime !== Carbon::parse($afterTimeStr)->format('H:i')) {
                        $rest->update([
                            $time->status === 3 ? 'start_time' : 'end_time'
                            => Carbon::parse($workDate)->setTimeFromTimeString($afterTimeStr),
                        ]);
                    }
                }
            }
            $startRestNew = optional(
                $workRequest->times->whereNull('rest_id')->firstWhere('status', 5)
            )->after_time;
            $endRestNew = optional(
                $workRequest->times->whereNull('rest_id')->firstWhere('status', 6)
            )->after_time;

            if($startRestNew && $endRestNew) {
                $start = Carbon::parse($workDate)->setTimeFromTimeString($startRestNew)->startOfMinute();
                $end = Carbon::parse($workDate)->setTimeFromTimeString($endRestNew)->startOfMinute();

                $exists = $work->rests->contains(function ($rest) use ($start, $end) {
                    return Carbon::parse($rest->start_time)->startOfMinute()->eq($start) &&
                    Carbon::parse($rest->end_time)->startOfMinute()->eq($end);
                });

                if(!$exists) {
                    $newRest = new Rest([
                        'user_id' => $workRequest->user_id,
                        'start_time' => $startRestNew,
                        'end_time' => $endRestNew,
                    ]);
                    $work->rests()->save($newRest);
                }
            }
        });
        return redirect("/stamp_correction_request/approve/{$workRequestId}");
    }

    public function export(Request $request, $userId)
    {
        $user = User::find($userId);
        $month = $request->input('date');
        $date = $date = $month ? Carbon::parse($month) : Carbon::now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $works = Work::with('rests')
        ->where('user_id', $userId)
        ->whereBetween('date', [$startOfMonth, $endOfMonth])
        ->get();
        $columns = ['日付', '出勤', '退勤', '休憩', '合計'];

        $response = new StreamedResponse(function () use ($works, $columns) {
            $file = fopen('php://output', 'w');
            mb_convert_variables('SJIS-win', 'UTF-8', $columns);
            fputcsv($file, $columns);

            foreach ($works as $work) {
                $row = [
                    optional($work->date)->format('Y-m-d'),
                    optional($work->start_time)->format('H:i'),
                    optional($work->end_time)->format('H:i'),
                    $work->totalRestTimeFormat() ?? '',
                    $work->totalWorkTimeFormat() ?? '',
                ];
                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($file, $row);
            }
            fclose($file);
        });
        $fileName = rawurlencode("{$user->name}_{$date->format('Y-m')}_attendance.csv");
        $response->headers->set('Content-Type', 'text/csv; charset=Shift_JIS');
        $response->headers->set('Content-Disposition', "attachment; filename={$fileName}");
        return $response;
    }
}
