<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;

class StaffListTest extends TestCase
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
                $base->copy()->subMonth(),
                $base->copy(),
                $base->copy()->addMonth(),
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

    public function test_can_see_users()
    {
        $response = $this->get('/admin/staff/list');
        foreach($this->user as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
            $response->assertSee('詳細');
        }
    }

    public function test_can_see_selected_user_attendance()
    {
        $user = $this->user->first();
        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $work = $user->works()
        ->whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->first();
        $response->assertSee($user->name);
        $response->assertSee($work->start_time->format('H:i'));
        $response->assertSee($work->end_time->format('H:i'));
        $response->assertSee($work->totalWorkTimeFormat());
        $response->assertSee($work->totalRestTimeFormat());
    }

    public function test_can_see_previous_month_attendance()
    {
        $user = $this->user->first();
        $previousMonth = Carbon::now()->copy()->subMonth();
        $url = $previousMonth->format('Y-m');

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?date=' . $url);
        $work = $user->works()
        ->whereYear('date', $previousMonth->year)
        ->whereMonth('date', $previousMonth->month)
        ->first();
        $response->assertSee($work->date->format('m/j'));
        $response->assertSee($work->start_time->format('H:i'));
        $response->assertSee($work->end_time->format('H:i'));
        $response->assertSee($work->totalWorkTimeFormat());
        $response->assertSee($work->totalRestTimeFormat());
    }

    public function test_can_see_next_month_attendance()
    {
        $user = $this->user->first();
        $nextMonth = Carbon::now()->copy()->addMonth();
        $url = $nextMonth->format('Y-m');

        $response = $this->get('/admin/attendance/staff/' . $user->id . '?date=' . $url);
        $work = $user->works()
        ->whereYear('date', $nextMonth->year)
        ->whereMonth('date', $nextMonth->month)
        ->first();
        $response->assertSee($work->date->format('m/j'));
        $response->assertSee($work->start_time->format('H:i'));
        $response->assertSee($work->end_time->format('H:i'));
        $response->assertSee($work->totalWorkTimeFormat());
        $response->assertSee($work->totalRestTimeFormat());
    }

    public function test_can_see_detail()
    {
        $user = $this->user->first();
        $response = $this->get('/admin/attendance/staff/' . $user->id);
        $work = $user->works()
        ->whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->first();
        $response->assertSee('詳細');

        $response = $this->get('/attendance/' . $work->id);
        $response->assertSee($user->name);
        $response->assertSee($work->date->format('Y年'));
        $response->assertSee($work->date->format('n月j日'));
        $response->assertSee($work->start_time->format('H:i'));
        $response->assertSee($work->end_time->format('H:i'));
    }
}
