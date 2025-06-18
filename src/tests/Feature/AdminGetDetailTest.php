<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;

class AdminGetDetailTest extends TestCase
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

        $this->user = User::factory()->create();

        $work = Work::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now()->setTIme(9, 0),
            'end_time' => Carbon::now()->setTIme(18, 0),
        ]);
        Rest::factory()->create([
            'work_id' => $work->id,
            'user_id' => $this->user->id,
            'start_time' => Carbon::now()->setTIme(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
        ]);
    }

    public function test_can_see_detail()
    {
        $response = $this->get('/admin/attendance/list');
        $response->assertSee($this->user->name);
        $response->assertSee('詳細');

        $work = $this->user->works()->first();

        $response = $this->get('/attendance/' . $work->id);
        $response->assertSee($this->user->name);
        $response->assertSee($work->date->format('Y年'));
        $response->assertSee($work->date->format('n月j日'));
    }

    public function test_start_work_is_later()
    {
        $work = $this->user->works()->first();
        $response = $this->post('/attendance/' . $work->id, [
            'work_id' => $work->id,
            'start_work' => '19:00',
            'end_work' => '9:00',
            'remarks' => 'test',
        ]);
        $response->assertSessionHasErrors([
            'end_work' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_start_rest_is_later()
    {
        $work = $this->user->works()->first();
        $response = $this->post('/attendance/' . $work->id, [
            'work_id' => $work->id,
            'start_work' => '10:00',
            'end_work' => '19:00',
            'start_rest' => ['20:00'],
            'end_rest' => ['21:00'],
            'remarks' => 'test',
        ]);
        $response->assertSessionHasErrors([
            'end_rest.0' => '休憩が勤務時間外です',
        ]);
    }

    public function test_end_rest_is_later()
    {
        $work = $this->user->works()->first();
        $response = $this->post('/attendance/' . $work->id, [
            'work_id' => $work->id,
            'start_work' => '10:00',
            'end_work' => '19:00',
            'start_rest' => ['18:00'],
            'end_rest' => ['20:00'],
            'remarks' => 'test',
        ]);
        $response->assertSessionHasErrors([
            'end_rest.0' => '休憩が勤務時間外です',
        ]);
    }

    public function test_remarks_is_required()
    {
        $work = $this->user->works()->first();
        $response = $this->post('/attendance/' . $work->id, [
            'work_id' => $work->id,
            'start_work' => '10:00',
            'end_time' => '19:00',
            'start_rest' => ['14:00'],
            'end_rest' => ['15:00'],
            'remarks' => '',
        ]);
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }
}
