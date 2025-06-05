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

        $loginType = session('login_type');
        if ($loginType === 'admin') {
            return view('admin.detail', compact('user', 'work', 'workRequest', 'startWork', 'endWork', 'restRequestTimes', 'startRestNew', 'endRestNew'));
        } elseif ($loginType === 'user') {
            return view('user.detail', compact('user', 'work', 'workRequest', 'startWork', 'endWork', 'restRequestTimes', 'startRestNew', 'endRestNew'));
        }
    }

    public function store(UpdateRequest $request, $workId)
    {
        $loginType = session('login_type');

        if ($loginType === 'user') {
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
                $this->createRequestTime($workRequest->id, null, 2, $work->end_time, $request->input('end_work'), 'end');
            }

            foreach ($work->rests as $index => $rest) {
                $startInput = $request->input("start_rest.$index");
                $endInput = $request->input("end_rest.$index");

                if ($startInput) {
                    $this->createRequestTime($workRequest->id, $rest->id, 3, $rest->start_time, $startInput, 'start');
                }
                if ($endInput) {
                    $this->createRequestTime($workRequest->id, $rest->id, 4, $rest->end_time, $endInput, 'end');
                }
            }
            $lastIndex = $work->rests->count();
            $newStart = $request->input("start_rest.$lastIndex");
            $newEnd = $request->input("end_rest.$lastIndex");

            if ($newStart && $newEnd) {
                $this->createRequestTime($workRequest->id, null, 3, null, $newStart, 'start');
                $this->createRequestTime($workRequest->id, null, 4, null, $newEnd, 'end');
            }

        } elseif ($loginType === 'admin') {
            $user = Auth::user();
            $work = Work::with('rests', 'user')
            ->where('id', $workId)
            ->first();
            $existingRequest = WorkRequest::where('work_id', $workId)->first();

            if ($existingRequest) {
                // 既存レコードがある場合は更新でOK？
                $existingRequest->update([
                    'status' => 1,
                    'reviewed_by_user_id' => $user->id,
                    'reviewed_at' => Carbon::now(),
                    'remarks' => $request->remarks,
                ]);
                $workRequest = $existingRequest;
            } else {
                $workRequest = WorkRequest::create([
                    'work_id' => $workId,
                    'user_id' => $user->id,
                    'remarks' => $request->remarks,
                    'status' => 1,
                    'reviewed_by_user_id' => $user->id,
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            if ($request->filled('start_work')) {
                $this->createRequestTime($workRequest->id, null, 1, $work->start_time, $request->input('start_work'), 'start');
            }
            if ($request->filled('end_work')) {
                $this->createRequestTime($workRequest->id, null, 2, $work->end_time, $request->input('end_work'), 'end');
            }

            foreach ($work->rests as $index => $rest) {
                $startInput = $request->input("start_rest.$index");
                $endInput   = $request->input("end_rest.$index");

                if ($startInput) {
                    $this->createRequestTime($workRequest->id, $rest->id, 3, $rest->start_time, $startInput, 'start');
                }
                if ($endInput) {
                    $this->createRequestTime($workRequest->id, $rest->id, 4, $rest->end_time, $endInput, 'end');
                }
            }
            $lastIndex = $work->rests->count();
            $newStart = $request->input("start_rest.$lastIndex");
            $newEnd = $request->input("end_rest.$lastIndex");
            if ($newStart && $newEnd) {
                $this->createRequestTime($workRequest->id, null, 3, null, $newStart, 'start');
                $this->createRequestTime($workRequest->id, null, 4, null, $newEnd, 'end');
            }
        }
        return redirect("/attendance/{$workId}");
    }

    private function createRequestTime($workRequestId, $restId, $status, $beforeTime, $afterTime)
    {
        $after = Carbon::createFromFormat('H:i', $afterTime);

        if ($beforeTime instanceof Carbon) {
            $before = $beforeTime->format('H:i');
        }elseif (is_string($beforeTime)) {
            $before = Carbon::createFromFormat('H:i:s', $beforeTime)->format('H:i');
        }else {
            $before = null;
        }

        // 前回値と同じであれば登録をスキップ（新規追加以外）
        if (!is_null($before) && $before === $after->format('H:i')) {
            return;
        }

        WorkRequestTime::create([
            'work_request_id' => $workRequestId,
            'rest_id' => $restId,
            'status' => $status,
            'before_time' => $beforeTime,
            'after_time' => $after,
        ]);
    }
}
