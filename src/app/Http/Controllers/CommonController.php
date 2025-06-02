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
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;


class CommonController extends Controller
{
    public function request(Request $request)
    {
        $loginType = session('login_type');

        if ($loginType === 'user') {
            return app(UserController::class)->requestList($request);
        } elseif ($loginType === 'admin') {
            return app(AdminController::class)->adminRequestList($request);
        }
    }

    public function detail($workId)
    {
        $user = Auth::user();
        $work = Work::with('user', 'rests')
        ->where('id', $workId)
        ->first();
        $workRequest = WorkRequest::where('work_id', $workId)->first();
        $time = WorkRequestTime::with('rest', 'workRequest');

        $loginType = session('login_type');
        if($loginType === 'admin') {
            return view('admin.detail', compact('user', 'work', 'workRequest', 'time'));
        } elseif($loginType === 'user') {
            return view('user.detail', compact('user', 'work', 'workRequest', 'time'));
        }
    }

    public function store(UpdateRequest $request, $workId)
    {
        $loginType = session('login_type');

        if($loginType === 'user') {
            $user = Auth::user();
            $work = Work::with('rests')
            ->where('user_id', $user->id)
            ->where('id', $workId)
            ->first();

            $workRequest = WorkRequest::create([
                'work_id' => $workId,
                'user_id' => $user->id,
                'remarks' => $request->remarks,
                'status' => false,
            ]);

            if ($request->filled('start_work')) {
                $this->createRequestTime($workRequest->id, null, 1, $work->start_time, $request->input('start_work'), 'start');
            }

            if ($request->filled('end_work')) {
                $this->createRequestTime($workRequest->id, null, 1, $work->end_time, $request->input('end_work'), 'end');
            }

            // ③ 休憩時間の修正（複数対応）
            foreach ($work->rests as $index => $rest) {
                $startInput = $request->input("start_rest.$index");
                $endInput = $request->input("end_rest.$index");

                if ($startInput) {
                    $this->createRequestTime($workRequest->id, $rest->id, 2, $rest->start_time, $startInput, 'start');
                }
                if ($endInput) {
                    $this->createRequestTime($workRequest->id, $rest->id, 3, $rest->end_time, $endInput, 'end');
                }
            }

            // ④ 新規追加された休憩の処理
            $newStart = $request->input("start_rest." . $work->rests->count());
            $newEnd = $request->input("end_rest." . $work->rests->count());

            if ($newStart && $newEnd) {
                $this->createRequestTime($workRequest->id, null, 2, null, $newStart, 'start');
                $this->createRequestTime($workRequest->id, null, 3, null, $newEnd, 'end');
            }

        } elseif($loginType === 'admin') {
            $user = Auth::user();
            $work = Work::with('rests', 'user')
            ->where('id', $workId)
            ->get();

            $workRequest = WorkRequest::create([
                'work_id' => $workId,
                'user_id' => $user->id,
                'remarks' => $request->remarks,
                'status' => 1,
                'reviewed_by_user_id' => $user->id,
                'reviewed_at' => now(),
            ]);
        }
/*
        // 出勤・退勤
        if ($work->start_time->format('H:i') !== $request->start_work) {
            $afterStartWork = Carbon::createFromFormat('Y-m-d H:i', $work->start_time
            ->format('Y-m-d') . ' ' . $request->start_work);
            WorkRequestTime::create([
                'work_request_id' => $workRequest->id,
                'status' => 1,
                'before_time' => $work->start_time,
                'after_time' => $afterStartWork,
            ]);

        } elseif ($work->end_time->format('H:i') != $request->end_work) {
            $afterEndWork = Carbon::createFromFormat('Y-m-d H:i', $work->end_time
            ->format('Y-m-d') . ' ' . $request->end_work);
            WorkRequestTime::create([
                'work_request_id' => $workRequest->id,
                'status' => 0,
                'before_time' => $work->end_time,
                'after_time' => $afterEndWork,
            ]);
        }

        // 休憩
        $startRests = $request->input('start_rest', []);
        $endRests = $request->input('end_rest', []);

        foreach ($work->rests as $index => $rest){
            $startInput = $startRests[$index] ?? null;
            $endInput = $endRests[$index] ?? null;

            // 休憩開始
            if ($startInput && $rest->start_time->format('H:i') !== $startInput) {
                $afterStartRest = Carbon::createFromFormat('Y-m-d H:i', $rest->start_time
                ->format('Y-m-d') . ' ' . $startInput);
                WorkRequestTime::create([
                    'work_request_id' => $workRequest->id,
                    'rest_id' => $rest->id,
                    'status' => 2,
                    'before_time' => $rest->start_time,
                    'after_time' => $afterStartRest,
                ]);
            }
            // 休憩終了
            if ($endInput && $rest->end_time->format('H:i') !== $endInput) {
                $afterEndRest = Carbon::createFromFormat('Y-m-d H:i', $rest->end_time
                ->format('Y-m-d') . ' ' . $endInput);
                WorkRequestTime::create([
                    'work_request_id' => $workRequest->id,
                    'rest_id' => $rest->id,
                    'status' => 3,
                    'before_time' => $rest->end_time,
                    'after_time' => $afterEndRest,
                ]);
            }
        }
        // 新規の休憩
        for ($i = $work->rests->count(); $i < count($startRests); $i++) {
            $startInput = $startRests[$i] ?? null;
            $endInput = $endRests[$i] ?? null;

            if ($startInput || $endInput) {
                $date = $work->date->format('Y-m-d');
                // 開始
                if ($startInput) {
                    $afterStartRest = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $startInput);
                    WorkRequestTime::create([
                        'work_request_id' => $workRequest->id,
                        'rest_id' => null,
                        'status' => 2,
                        'before_time' => null,
                        'after_time' => $afterStartRest,
                    ]);
                }
                // 終了
                if ($endInput) {
                    $afterEndRest = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $endInput);
                    WorkRequestTime::create([
                        'work_request_id' => $workRequest->id,
                        'rest_id' => null,
                        'status' => 3,
                        'before_time' => null,
                        'after_time' => $afterEndRest,
                    ]);
                }
            }
        }
            */
        return redirect("/attendance/{$workId}");
    }

    private function createRequestTime($workRequestId, $restId, $status, $beforeTime, $afterTime)
    {
        WorkRequestTime::create([
            'work_request_id' => $workRequestId,
            'rest_id' => $restId,
            'status' => $status,
            'before_time' => $beforeTime,
            'after_time' => Carbon::createFromFormat('H:i', $afterTime),
        ]);
    }
}
