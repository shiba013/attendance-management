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

class CommonController extends Controller
{
    public function detail($workId)
    {
        $user = Auth::user();
        $work = Work::with('user', 'rests')
        ->where('id', $workId)
        ->first();
        $workRequest = WorkRequest::where('work_id', $workId)->first();

        $loginType = session('login_type');
        if($loginType === 'admin') {
            return view('admin.detail', compact('user', 'work', 'workRequest'));
        } elseif($loginType === 'user') {
            return view('user.detail', compact('user', 'work', 'workRequest'));
        }
    }

    public function update(UpdateRequest $request, $workId)
    {
        $user = Auth::user();
        $work = Work::with('rests')
        ->where('user_id', $user->id)
        ->where('id', $workId)
        ->first();

        $workRequest = WorkRequest::create([
            'work_id' => $workId,
            'user_id' => $user->id,
            'remarks' => $request->remarks,
            'status' => 0,
        ]);

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
                // 開始
                if ($startInput && (! $rest->start_time || $rest->start_time
                ->format('H:i') !== $startInput)) {
                    $date = $rest->start_time ? $rest->start_time->format('Y-m-d') : $work->work_date->format('Y-m-d');
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
                if ($startInput && (! $rest->start_time || $rest->start_time
                ->format('H:i') !== $startInput)) {
                    $date = $rest->start_time ? $rest->start_time->format('Y-m-d') : $work->work_date->format('Y-m-d');
                    $afterStartRest = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $startInput);
                    WorkRequestTime::create([
                        'work_request_id' => $workRequest->id,
                        'rest_id' => null,
                        'status' => 2,
                        'before_time' => null,
                        'after_time' => $afterStartRest,
                    ]);
                }
            }
        }
        return redirect("/attendance/{$workId}");
    }
}
