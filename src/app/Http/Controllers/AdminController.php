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
        $time = WorkRequestTime::with('rest', 'workRequest')
        ->find('id');
        return view('admin.approval', compact('workRequest', 'time'));
    }

    public function update(UpdateRequest $request, $workRequestId)
    {
        $workRequest = WorkRequest::with(['user', 'work.rests', 'times'])
        ->find($workRequestId);

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
                        $work->update([
                            $time->after_time < $work->start_time ? 'start_time' : 'end_time' => $time->after_time,
                        ]);
                    }
                }

                if ($time->rest_id && in_array($time->status, [2, 3])) {
                    $rest = $time->rest;
                    $rest->update([
                        $time->status === 2 ? 'start_time' : 'end_time' => $time->after_time,
                    ]);
                }

                if (!$time->rest_id && in_array($time->status, [2, 3])) {
                    $newRest = new Rest([
                        'user_id' => $request->user_id,
                        'start_time' => $time->status === 2 ? $time->after_time : null,
                        'end_time' => $time->status === 3 ? $time->after_time : null,
                    ]);
                    $request->work->rests()->save($newRest);
                }
            }
        });
        return redirect("/stamp_correction_request/approve/{$workRequestId}");
    }
}
