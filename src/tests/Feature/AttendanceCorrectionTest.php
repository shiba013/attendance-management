<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use App\Models\WorkRequest;
use App\Models\WorkRequestTime;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function setUp(): void
    {
        Parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 1,
        ]);
        $this->actingAs($this->admin);
        $this->withSession(['login_type' => 'admin']);

        $this->user = User::factory(3)->create();
        $this->workRequest = collect();

        foreach($this->user as $user) {
            $work = Work::factory()->create([
                'user_id' => $user->id,
            ]);
            $rest = Rest::factory()->create([
                'work_id' => $work->id,
                'user_id' => $user->id,
            ]);
            $workRequest = WorkRequest::factory()->create([
                'work_id' => $work->id,
                'user_id' => $user->id,
                'status' => 0,
                'remarks' => 'test',
            ]);
            $workRequestTime = WorkRequestTime::factory()->create([
                'work_request_id' => $workRequest->id,
                'rest_id' => null,
                'status' => 2,
                'before_time' => '18:00',
                'after_time' => '19:00',
            ]);
            $this->workRequest->push($workRequest);
        }
    }

    public function test_can_see_pending_requests_list()
    {
        $response = $this->get('/stamp_correction_request/list');
        foreach($this->workRequest as $workRequest) {
            $response->assertSee('承認待ち');
            $response->assertSee($workRequest->user->name);
            $response->assertSee($workRequest->work->date->format('Y/m/d'));
            $response->assertSee($workRequest->remarks);
        }
    }

    public function test_can_see_approve_requests_list()
    {
        $this->workRequest->each(function ($workRequest) {
            $workRequest->update([
                'reviewed_by_user_id' => $this->admin->id,
                'status' => 1,
            ]);
        });
        $response = $this->get('/stamp_correction_request/list/?tab=done');
        foreach($this->workRequest as $workRequest) {
            $response->assertSee('承認済み');
            $response->assertSee($workRequest->user->name);
            $response->assertSee($workRequest->work->date->format('Y/m/d'));
            $response->assertSee($workRequest->remarks);
        }
    }

    public function test_can_see_request_detail()
    {
        $response = $this->get('/stamp_correction_request/list');
        foreach($this->workRequest as $workRequest) {
            $response->assertSee('詳細');

            $response = $this->get('/stamp_correction_request/approve/' . $workRequest->id);
            $workRequestTime = $workRequest->times->first();
            $after_time = Carbon::parse($workRequestTime->after_time);

            $rest = $workRequest->work->rests->first();
            $startRest = Carbon::parse($rest->start_time);
            $endRest = Carbon::parse($rest->end_time);

            $response->assertSee($workRequest->user->name);
            $response->assertSee($workRequest->work->date->format('Y年'));
            $response->assertSee($workRequest->work->date->format('n月j日'));
            $response->assertSee($workRequest->work->start_time->format('H:i'));
            $response->assertSee($after_time->format('H:i'));
            $response->assertSee($startRest->format('H:i'));
            $response->assertSee($endRest->format('H:i'));
            $response->assertSee($workRequest->remarks);
        }
    }

    public function test_approve_is_success()
    {
        foreach($this->workRequest as $workRequest) {
            $postData = [
                'start_work' => '9:00',
                'end_work' => '19:00',
                'start_rest' => ['12:00'],
                'end_rest' => ['13:00'],
                'remarks' => 'test',
            ];
            $response = $this->followingRedirects()
            ->post('/stamp_correction_request/approve/' . $workRequest->id, $postData);
            $response->assertSee('承認済み');

            $this->assertDatabaseHas('work_requests', [
                'id' => $workRequest->id,
                'reviewed_by_user_id' => $this->admin->id,
                'status' => 1,
                'remarks' => 'test',
            ]);
            $this->assertDatabaseHas('works', [
                'date' => $workRequest->work->date->format('Y-m-d'),
                'start_time' => Carbon::parse('9:00'),
                'end_time' => Carbon::parse('19:00'),
            ]);
            $this->assertDatabaseHas('rests', [
                'work_id' => $workRequest->work->id,
                'start_time' => Carbon::parse('12:00'),
                'end_time' => Carbon::parse('13:00'),
            ]);
            $this->assertDatabaseHas('work_request_times', [
                'work_request_id' =>$workRequest->id,
                'before_time' => Carbon::parse('18:00'),
                'after_time' => Carbon::parse('19:00'),
                'status' => 2,
            ]);
        }
    }
}
