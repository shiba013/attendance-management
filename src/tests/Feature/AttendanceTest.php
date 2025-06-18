<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //日時取得機能
    public function test_get_clock()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 12, 30, 0));
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withSession(['login_type' => 'user']);

        $response = $this->get('/attendance');
        $date = '2025年1月1日(水)';
        $time = '12:30';
        $response->assertSee($date);
        $response->assertSee($time);
    }

    //ステータス確認機能
    private function work_status($status)
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withSession(['login_type' => 'user']);

        Work::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => $status,
        ]);
        return $user;
    }

    public function test_status_is_off()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withSession(['login_type' => 'user']);

        $this->get('/attendance')->assertSee('勤務外');
    }

    public function test_status_is_working()
    {
        $this->work_status(1);
        $this->get('/attendance')->assertSee('勤務中');
    }

        public function test_status_is_rest()
    {
        $this->work_status(3);
        $this->get('/attendance')->assertSee('休憩中');
    }

        public function test_status_is_done()
    {
        $this->work_status(2);
        $this->get('/attendance')->assertSee('退勤済');
    }

    //出勤機能
    public function test_start_work()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withSession(['login_type' => 'user']);

        $this->get('/attendance')->assertSee('出勤');

        $response = $this->post('/attendance', ['start_work' => true]);
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    public function test_work_is_one_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withSession(['login_type' => 'user']);

        Work::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHours(9),
            'status' => 2,
        ]);
        $this->get('/attendance')->assertDontSee('出勤');
    }

    public function test_work_time_appears_in_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withSession(['login_type' => 'user']);

        $start = Carbon::now()->setTime(9, 0, 0);
        Work::factory()->create([
            'user_id' => $user->id,
            'date' => $start->format('Y-m-d'),
            'start_time' => $start,
            'status' => 1,
        ]);
        $this->get('/attendance/list')->assertSee($start->format('H:i'));
    }

    private function start_work()
    {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->withSession(['login_type' => 'user']);

        $this->work = Work::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now(),
            'status' => 1,
        ]);
    }

    //休憩機能
    public function test_start_rest()
    {
        $this->start_work();
        $this->get('/attendance')->assertSee('休憩入');
        $this->post('/attendance', ['start_rest' => true]);
        $this->get('/attendance')->assertSee('休憩中');
    }

    public function test_can_start_rest_multiple()
    {
        $this->start_work();
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->post('/attendance', ['start_rest' => true]);
        $this->get('/attendance')->assertSee('休憩戻');
    }

    public function test_end_rest()
    {
        $this->start_work();
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->get('/attendance')->assertSee('勤務中');
    }

    public function test_end_rest_multiple()
    {
        $this->start_work();
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->get('/attendance')->assertSee('休憩入');
    }

    public function test_rest_time_appears_in_list()
    {
        $this->start_work();
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);

        $start = Carbon::now()->addHour()->second(0);
        $end = Carbon::now()->addHours(2)->second(0);
        Rest::create([
            'user_id' => $this->user->id,
            'work_id' => Work::where('user_id', $this->user->id)->first()->id,
            'start_time' => $start,
            'end_time' => $end,
        ]);
        $rest = $end->diff($start)->format('%H:%i');

        $this->get('/attendance/list')->assertSee($rest);
    }

    //退勤機能
    public function test_done()
    {
        $this->start_work();
        $this->get('/attendance')->assertSee('退勤');
        $this->post('/attendance', ['end_work' => true]);
        $this->get('/attendance')->assertSee('退勤済');
    }

    public function test_done_time_appears_in_list()
    {
        $this->start_work();
        $end = Carbon::now()->addHours(8)->second(0);
        $this->work->update([
            'end_time' => $end,
            'status' => 2,
        ]);
        $this->get('/attendance/list')->assertSee($end->format('H:i'));
    }
}
