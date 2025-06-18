<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;

class AdminAttendanceListTest extends TestCase
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

        $base = Carbon::now();
        foreach($this->user as $user) {
            $dates = [
                $base->copy()->subDay(),
                $base->copy(),
                $base->copy()->addDay(),
            ];
            foreach($dates as $date) {
                $work = Work::factory()->create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $date->copy()->setTime(9, 0),
                    'end_time' => $date->copy()->setTime(18, 0),
                ]);
                Rest::factory()->create([
                    'work_id' => $work->id,
                    'user_id' => $user->id,
                    'start_time' => $date->copy()->setTime(12, 0),
                    'end_time' => $date->copy()->setTime(13, 0),
                ]);
            }
        }
    }

    public function test_can_see_today_attendance()
    {
        $response = $this->get('/admin/attendance/list');
        $response->assertSee(now()->format('Y年n月j日'));
        foreach($this->user as $user) {
            $response->assertSee($user->name);

            $work = $user->works()
            ->where('date', now()->format('Y-m-d'))
            ->first();
            $response->assertSee($work->start_time->format('H:i'));
            $response->assertSee($work->end_time->format('H:i'));
            $response->assertSee($work->totalWorkTimeFormat());
            $response->assertSee($work->totalRestTimeFormat());
        };
    }

    public function test_can_see_previous_day_attendance()
    {
        $response = $this->get('/admin/attendance/list')
        ->assertSee('前日');

        $yesterday = Carbon::now()->copy()->subDay();
        $url = $yesterday->format('Y-m-d');
        $response = $this->get('/admin/attendance/list?date=' . $url);
        $response->assertSee($yesterday->format('Y年n月j日'));
        foreach($this->user as $user) {
            $response->assertSee($user->name);

            $work = $user->works()
            ->where('date', $yesterday->format('Y-m-d'))
            ->first();
            $response->assertSee($work->start_time->format('H:i'));
            $response->assertSee($work->end_time->format('H:i'));
            $response->assertSee($work->totalWorkTimeFormat());
            $response->assertSee($work->totalRestTimeFormat());
        }
    }

    public function test_can_see_next_day_attendance()
    {
        $response = $this->get('/admin/attendance/list')
        ->assertSee('翌日');

        $tomorrow = Carbon::now()->copy()->addDay();
        $url = $tomorrow->format('Y-m-d');
        $response = $this->get('/admin/attendance/list?date=' . $url);
        $response->assertSee($tomorrow->format('Y年n月j日'));
        foreach($this->user as $user) {
            $response->assertSee($user->name);

            $work = $user->works()
            ->where('date', $tomorrow->format('Y-m-d'))
            ->first();
            $response->assertSee($work->start_time->format('H:i'));
            $response->assertSee($work->end_time->format('H:i'));
            $response->assertSee($work->totalWorkTimeFormat());
            $response->assertSee($work->totalRestTimeFormat());
        }
    }
}
