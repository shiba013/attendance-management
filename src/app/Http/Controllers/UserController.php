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

class UserController extends Controller
{
    public function index()
    {
        $user = Auth::User();
        $today = Carbon::now()->format('Y-m-d');
        $work = Work::where('user_id', $user->id)->where('date', $today)->first();

        if(!$work){
            $status = 0;
        } else {
            $status = $work->status;
        }
        return view('user.attendance', compact('user', 'status'));
    }

    public function stamping(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        $now_time = Carbon::now();

        if ($request->has('start_work')) {
            $work = Work::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'start_time' => $now_time,
                    'status' => 1,
                ],
            );

        } elseif ($request->has('end_work')) {
            $work = Work::where('user_id', $user->id)
            ->where('date', $today)->first();
            if($work) {
                $work->update([
                    'end_time' => $now_time,
                    'status' => 2,
                ]);
            }

        } elseif ($request->has('start_rest')) {
            $work = Work::where('user_id', $user->id)
            ->where('date', $today)->first();
            if($work) {
                Rest::create([
                    'work_id' => $work->id,
                    'user_id' => $user->id,
                    'start_time' => $now_time,
                ]);
                $work->update(['status' => 3]);
            }

        } elseif ($request->has('end_rest')) {
            $rest = Rest::where('user_id', $user->id)
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();
            if($rest) {
                $rest->update(['end_time' => $now_time]);
                $work = Work::find($rest->work_id);
                if($work) {
                    $work->update(['status' => 4]);
                }
            }
        }
        return redirect('/attendance');
    }

    public function attendanceList(Request $request)
    {
        $user = Auth::user();

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
        ->where('user_id', $user->id)
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
        return view('user.attendance_list', compact('user', 'thisMonth', 'previousMonth', 'nextMonth','works', 'dailyWorks'));
    }

    public function requestList(Request $request)
    {
        $user = Auth::user();
        $query = WorkRequest::with('user', 'work', 'times')
        ->where('user_id', $user->id);
        $tab = $request->query('tab');
        if ($tab == 'done') {
            $query->where('status', 1);
        } elseif($tab == '') {
            $query->where('status', 0);
        }
        $corrections = $query->get();
        return view('request_list', compact('corrections', 'tab'));
    }
}
