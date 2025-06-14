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
    public function testGetClock()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 12, 30, 0));
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $date = '2025年1月1日(水)';
        $time = '12:30';
        $response->assertSee($date);
        $response->assertSee($time);
    }

    //ステータス確認機能
    private function workStatus($status)
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Work::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => $status,
        ]);
        return $user;
    }

    public function testStatusIsOff()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/attendance')->assertSee('勤務外');
    }

    public function testStatusIsWorking()
    {
        $this->workStatus(1);
        $this->get('/attendance')->assertSee('勤務中');
    }

        public function testStatusIsRest()
    {
        $this->workStatus(3);
        $this->get('/attendance')->assertSee('休憩中');
    }

        public function testStatusIsDone()
    {
        $this->workStatus(2);
        $this->get('/attendance')->assertSee('退勤済');
    }

    //出勤機能
    public function testStartWork()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/attendance')->assertSee('出勤');

        $response = $this->post('/attendance', ['start_work' => true]);
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    public function testWorkIsOneDay()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Work::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addHours(9),
            'status' => 2,
        ]);
        $this->get('/attendance')->assertDontSee('出勤');
    }

    public function testWorkTimeAppearsInList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $start = Carbon::now()->setTime(9, 0, 0);
        Work::factory()->create([
            'user_id' => $user->id,
            'date' => $start->format('Y-m-d'),
            'start_time' => $start,
            'status' => 1,
        ]);
        $this->get('/attendance/list')->assertSee($start->format('H:i'));
    }

    private function startWork()
    {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->work = Work::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now(),
            'status' => 1,
        ]);
    }

    //休憩機能
    public function testStartRest()
    {
        $this->startWork();
        $this->get('/attendance')->assertSee('休憩入');
        $this->post('/attendance', ['start_rest' => true]);
        $this->get('/attendance')->assertSee('休憩中');
    }

    public function testCanStartRestMultiple()
    {
        $this->startWork();
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->post('/attendance', ['start_rest' => true]);
        $this->get('/attendance')->assertSee('休憩戻');
    }

    public function testEndRest()
    {
        $this->startWork();
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->get('/attendance')->assertSee('勤務中');
    }

    public function testEndRestMultiple()
    {
        $this->startWork();
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->post('/attendance', ['start_rest' => true]);
        $this->post('/attendance', ['end_rest' => true]);
        $this->get('/attendance')->assertSee('休憩入');
    }

    public function testRestTimeAppearsInList()
    {
        $this->startWork();
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
    public function testDone()
    {
        $this->startWork();
        $this->get('/attendance')->assertSee('退勤');
        $this->post('/attendance', ['end_work' => true]);
        $this->get('/attendance')->assertSee('退勤済');
    }

    public function testDoneTimeAppearsInList()
    {
        $this->startWork();
        $end = Carbon::now()->addHours(8)->second(0);
        $this->work->update([
            'end_time' => $end,
            'status' => 2,
        ]);
        $this->get('/attendance/list')->assertSee($end->format('H:i'));
    }
}
