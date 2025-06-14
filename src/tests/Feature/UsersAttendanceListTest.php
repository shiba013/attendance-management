<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;

class UsersAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    private function japaneseDate(Carbon $date): string
    {
        $days = [
            'Sun' => '日',
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木',
            'Fri' => '金',
            'Sat' => '土',
        ];
        $day = $days[$date->format('D')];
        return $date->format('m/d') . "($day)";
    }

    public function testCanSeeRecords()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $works = Work::factory()->count(3)->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::now()->setTime(9, 0),
            'end_time' => Carbon::now()->setTime(18, 0),
            'status' => 2,
        ]);
        $startRest = Carbon::now()->setTime(12, 0);
        $endRest = Carbon::now()->setTime(13, 0);
        Rest::create([
            'user_id' => $user->id,
            'work_id' => $works->first()->id,
            'start_time' => $startRest,
            'end_time' => $endRest,
        ]);
        $rest = $endRest->diff($startRest)->format('%H:%i');

        $response = $this->get('/attendance/list');
        foreach($works as $work) {
            $formatDate = $this->japaneseDate($work->date);
            $response->assertSee($formatDate);
            $response->assertSee($work->start_time->format('H:i'));
            $response->assertSee($work->end_time->format('H:i'));
        }
        $response->assertSee($rest);
    }

    public function testCanSeeCurrentMonthList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $currentMonth = Carbon::now()->format('Y年n月');
        $date = Carbon::now()->startOfMonth()->format('Y-m-d');

        Work::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => Carbon::now()->setTime(9, 0),
            'end_time' => Carbon::now()->setTIme(18, 0),
            'status' => 2,
        ]);
        $formatDate = $this->japaneseDate(Carbon::parse($date));

        $response = $this->get('/attendance/list');
        $response->assertSee($currentMonth);
        $response->assertSee($formatDate);
    }

    public function testCanSeePreviousMonthList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $previousMonth = Carbon::now()->subMonth()->format('Y年n月');
        $url = Carbon::now()->subMonth()->format('Y-m');
        $date = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');

        Work::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => Carbon::now()->setTime(9, 0),
            'end_time' => Carbon::now()->setTime(18, 0),
            'status' => 2,
        ]);
        $formatDate = $this->japaneseDate(Carbon::parse($date));
        $this->get('/attendance/list')->assertSee('前月');

        $response = $this->get('/attendance/list?date=' . $url);
        $response->assertSee($previousMonth);
        $response->assertSee($formatDate);
    }

    public function testCanSeeNextMonthList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $nextMonth = Carbon::now()->addMonth()->format('Y年n月');
        $url = Carbon::now()->addMonth()->format('Y-m');
        $date = Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');

        Work::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => Carbon::now()->setTime(9, 0),
            'end_time' => Carbon::now()->setTime(18, 0),
            'status' => 2,
        ]);
        $formatDate = $this->japaneseDate(Carbon::parse($date));
        $this->get('/attendance/list')->assertSee('翌月');

        $response = $this->get('/attendance/list?date=' . $url);
        $response->assertSee($nextMonth);
        $response->assertSee($formatDate);
    }

    public function testCanSeeWorkDetail()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withSession(['login_type' => 'user']);

        $work = Work::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::now(),
            'start_time' => Carbon::now()->setTime(9, 0),
            'end_time' => Carbon::now()->setTime(18, 0),
            'status' => 2,
        ]);
        $year = $work->date->translatedFormat('Y年');
        $day = $work->date->translatedFormat('n月j日');

        $this->get('/attendance/list')->assertSee('詳細');
        $response = $this->get('/attendance/' . $work->id);
        $response->assertSee($year);
        $response->assertSee($day);
    }
}
