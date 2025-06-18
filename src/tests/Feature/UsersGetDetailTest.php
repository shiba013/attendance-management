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

class UsersGetDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    private function set_info()
    {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->withSession(['login_type' => 'user']);

        $this->work = Work::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->rest = Rest::factory()->create([
            'user_id' => $this->user->id,
            'work_id' => $this->work->id,
        ]);
    }

    private function set_admin()
    {
        $this->admin = User::factory()->create([
            'role' => 1,
        ]);
        $this->actingAs($this->admin);
        $this->withSession(['login_type' => 'admin']);
    }

    private function keep_requests()
    {
        $this->post('/attendance/' . $this->work->id, [
            'work_id' => $this->work->id,
            'start_work' => '09:00',
            'end_work' => '18:00',
            'start_rest' => ['12:00'],
            'end_rest' => ['13:00'],
            'remarks' => 'test',
        ]);
        $this->assertDatabaseHas('work_requests', [
            'user_id' => $this->user->id,
            'work_id' => $this->work->id,
            'status' => 0,
        ]);
        $response = $this->get('/attendance/' . $this->work->id);
        $response->assertSee('承認待ちのため修正はできません');
    }

    //ここからテスト内容
    public function test_start_work_is_later()
    {
        $this->set_info();
        $response = $this->post('/attendance/' . $this->work->id, [
            'work_id' => $this->work->id,
            'start_work' => '18:00',
            'end_work' => '09:00',
            'remarks' => 'test',
        ]);
        $response->assertSessionHasErrors([
            'end_work' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_start_rest_is_later()
    {
        $this->set_info();
        $response = $this->post('/attendance/' . $this->work->id, [
            'work_id' => $this->work->id,
            'start_work' => '09:00',
            'end_work' => '18:00',
            'start_rest' => ['19:00'],
            'end_rest' => ['20:00'],
            'remarks' => 'test',
        ]);
        $response->assertSessionHasErrors([
            "end_rest.0" => '休憩が勤務時間外です',
        ]);
    }

    public function test_end_rest_is_later()
    {
        $this->set_info();
        $response = $this->post('/attendance/' . $this->work->id, [
            'work_id' => $this->work->id,
            'start_work' => '09:00',
            'end_work' => '18:00',
            'start_rest' => ['17:00'],
            'end_rest' => ['19:00'],
            'remarks' => 'test',
        ]);
        $response->assertSessionHasErrors([
            "end_rest.0" => '休憩が勤務時間外です',
        ]);
    }

    public function test_remarks_is_required()
    {
        $this->set_info();
        $response = $this->post('/attendance/' . $this->work->id, [
            'work_id' => $this->work->id,
            'start_work' => '09:00',
            'end_work' => '18:00',
            'start_rest' => ['12:00'],
            'end_rest' => ['13:00'],
            'remarks' => '',
        ]);
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    public function test_request_is_success()
    {
        $this->set_info();
        $this->keep_requests();

        $workRequest = WorkRequest::where('work_id', $this->work->id)
        ->where('user_id', $this->user->id)
        ->where('status', 0)
        ->first();

        $this->set_admin();
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('test');
        $response->assertSee('詳細');

        $response = $this->get('/stamp_correction_request/approve/' . $workRequest->id);
        $response->assertSee('test');
        $response->assertSee('09:00');
    }

    public function test_can_see_my_requests()
    {
        $this->set_info();
        $requests = WorkRequest::factory(3)->create([
            'user_id' => $this->user->id,
            'remarks' => '自分の申請',
            'status' => 0,
        ]);
        foreach($requests as $request) {
            WorkRequestTIme::factory(3)->create([
                'work_request_id' => $request->id,
            ]);
        }
        $response = $this->get('/stamp_correction_request/list');
        $response->assertSee('承認待ち');
        $response->assertSee('自分の申請');
    }

    public function test_can_see_approved_requests()
    {
        $this->set_info();
        $requests = WorkRequest::factory(3)->create([
            'user_id' => $this->user->id,
            'remarks' => '自分の申請',
            'status' => 0,
        ]);
        foreach($requests as $request) {
            WorkRequestTime::factory(3)->create([
                'work_request_id' => $request->id,
            ]);
        }

        $this->set_admin();
        foreach($requests as $request) {
            $request->update([
                'reviewed_by_user_id' => $this->admin->id,
                'status' => 1,
            ]);
        }
        $response = $this->get('/stamp_correction_request/list/?tab=done');
        foreach($requests as $request) {
            $response->assertSee('承認済み');
            $response->assertSee($request->remarks);
        }
    }

    public function test_can_see_requests_detail()
    {
        $this->set_info();
        $works = Work::factory(3)->create([
            'user_id' => $this->user->id,
        ]);
        $requests = collect();
        foreach($works as $work) {
            $createRequests = WorkRequest::factory(3)->create([
                'user_id' => $this->user->id,
                'work_id' => $work->id,
                'remarks' => '修正申請の詳細確認',
            ]);
        }
        foreach($createRequests as $request) {
            $requests->push($request);
        }

        foreach($requests as $request) {
            WorkRequestTime::factory(3)->create([
                'work_request_id' => $request->id,
            ]);
        }
        foreach($requests as $request) {
            $response = $this->get('/stamp_correction_request/list')->assertSee('詳細');
            $response = $this->get('/attendance/' . $request->work_id);
            $response->assertSee($request->remarks);
        }
    }
}
