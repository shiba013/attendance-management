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
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;


class CommonController extends Controller
{
    public function request(Request $request)
    {
        $loginType = session('login_type');

        if($loginType === 'user') {
            return app(UserController::class)->requestList($request);
        } elseif($loginType === 'admin') {
            return app(AdminController::class)->adminRequestList($request);
        }
    }

    public function detail($workId)
    {
        $loginType = session('login_type');
        $user = Auth::user();
        if($user->role === 1) {
            $work = Work::with('user', 'rests')
            ->find($workId);
        } elseif($user->role === 0) {
            $work = Work::with('user', 'rests')
                ->where('id', $workId)
                ->where('user_id', $user->id)
                ->first();
        }
        $workRequest = WorkRequest::where('work_id', $workId)
        ->latest('created_at')
        ->first();

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
            $newEndRest = optional($times->firstWhere('status', 6))->after_time;

            $restRequestTimes['new'] = [
                'start_rest' => $newStartRest ? $newStartRest->format('H:i') : '',
                'end_rest'   => $newEndRest ? $newEndRest->format('H:i') : '',
            ];
            $startRestNew = $restRequestTimes['new']['start_rest'] ?? '';
            $endRestNew = $restRequestTimes['new']['end_rest'] ?? '';
        }

        if($loginType === 'admin') {
            return view('admin.detail', compact('user', 'work', 'workRequest', 'startWork', 'endWork', 'restRequestTimes', 'startRestNew', 'endRestNew'));
        } elseif($loginType === 'user') {
            return view('user.detail', compact('user', 'work', 'workRequest', 'startWork', 'endWork', 'restRequestTimes', 'startRestNew', 'endRestNew'));
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
                'status' => 0,
            ]);
            $this->handleRequestTimes($workRequest, $work, $request);

        } elseif($loginType === 'admin') {
            $user = Auth::user();

            DB::transaction(function () use ($request, $workId, $user) {
                $work = Work::with('rests', 'user')
                ->where('id', $workId)
                ->first();
                $existingRequest = WorkRequest::where('work_id', $workId)->first();

                if($existingRequest) {
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
                $this->handleRequestTimes($workRequest, $work, $request);

                $work->update([
                    'start_time' => $request->filled('start_work') ? Carbon::parse($work->date)->setTimeFromTimeString($request->input('start_work')) : $work->start_time,
                    'end_time' => $request->filled('end_work') ? Carbon::parse($work->date)->setTimeFromTimeString($request->input('end_work')) : $work->end_time,
                ]);

                foreach($work->rests as $index => $rest) {
                    $startRest = WorkRequestTime::where('work_request_id', $workRequest->id)
                    ->where('rest_id', $rest->id)
                    ->where('status', 3)
                    ->value('after_time');
                    $endRest = WorkRequestTime::where('work_request_id', $workRequest->id)
                    ->where('rest_id', $rest->id)
                    ->where('status', 4)
                    ->value('after_time');

                    $updates = [];
                    if(($startRest)) {
                        $newStart = Carbon::parse($work->date)
                        ->setTimeFromTimeString($startRest)->format('Y-m-d H:i:s');
                        $currentStart = optional($rest->start_time)->format('Y-m-d H:i:s');
                        if($newStart !== $currentStart) {
                            $updates['start_time'] = $newStart;
                        }
                    }
                    if(($endRest)) {
                        $newEnd = Carbon::parse($work->date)
                        ->setTimeFromTimeString($endRest)->format('Y-m-d H:i:s');
                        $currentEnd = optional($rest->end_time)->format('Y-m-d H:i:s');
                        if($newEnd !== $currentEnd) {
                            $updates['end_time'] = $newEnd;
                        }
                    }
                    if(!empty($updates)) {
                        $rest->update($updates);
                    }
                }
                $newStart = WorkRequestTime::Where('work_request_id', $workRequest->id)
                ->where('status', 5)
                ->value('after_time');
                $newEnd = WorkRequestTime::where('work_request_id', $workRequest->id)
                ->where('status', 6)
                ->value('after_time');

                if($newStart && $newEnd) {
                    $work->rests()->create([
                        'user_id' => $work->user_id,
                        'start_time' => Carbon::parse($work->date)
                        ->setTimeFromTimeString($newStart),
                        'end_time' => Carbon::parse($work->date)
                        ->setTimeFromTimeString($newEnd),
                    ]);
                }
            });
        }
        return redirect("/attendance/{$workId}");
    }

    private function handleRequestTimes(WorkRequest $workRequest, Work $work, UpdateRequest $request)
    {
        $workDate = $work->date;
        if($request->filled('start_work')) {
            $this->createRequestTime($workRequest->id, null, 1, $work->start_time, $request->input('start_work'),  $workDate);
        }
        if($request->filled('end_work')) {
            $this->createRequestTime($workRequest->id, null, 2, $work->end_time, $request->input('end_work'), $workDate);
        }

        foreach($work->rests as $index => $rest) {
            $startInput = $request->input("start_rest.$index");
            $endInput   = $request->input("end_rest.$index");

            if($startInput) {
                $this->createRequestTime($workRequest->id, $rest->id, 3, $rest->start_time, $startInput, $workDate);
            }
            if($endInput) {
                $this->createRequestTime($workRequest->id, $rest->id, 4, $rest->end_time, $endInput, $workDate);
            }
        }

        $lastIndex = $work->rests->count();
        $newStart = $request->input("start_rest.$lastIndex");
        $newEnd = $request->input("end_rest.$lastIndex");

        if($newStart && $newEnd) {
            $this->createRequestTime($workRequest->id, null, 5, null, $newStart, $workDate);
            $this->createRequestTime($workRequest->id, null, 6, null, $newEnd, $workDate);
        }
    }

    private function createRequestTime($workRequestId, $restId, $status, $beforeTime, $afterTime, $workDate)
    {
        $after = Carbon::parse($workDate)->setTimeFromTimeString($afterTime);

        if($beforeTime instanceof Carbon) {
            $before = $beforeTime;
        } elseif(is_string($beforeTime)) {
            $before = Carbon::parse($workDate)->setTimeFromTimeString($beforeTime);
        } else {
            $before = null;
        }

        if(!is_null($before) && $before === $after->format('H:i')) {
            return;
        }

        WorkRequestTime::create([
            'work_request_id' => $workRequestId,
            'rest_id' => $restId,
            'status' => $status,
            'before_time' => $before,
            'after_time' => $after,
        ]);
    }
}
