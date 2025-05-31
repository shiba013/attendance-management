<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $base = Carbon::now()->startOfMonth();
        $startDate = $base->copy()->subMonth()->startOfMonth();
        $endDate = $base->copy()->subMonth()->endOfMonth();
        $rests = [];
        $userId = 1;
        for ($date = $startDate->copy(); $date->lte($endDate) ; $date->addDay()) {
            if($date->isWeekend()) {
                continue;
            }

            $work = DB::table('works')->where('user_id', $userId)
            ->whereDate('date', $date->format('Y-m-d'))
            ->first();

            $rests[] = [
                'work_id' => $work->id,
                'user_id' => $userId,
                'start_time' => $date->copy()->setTime(12, 0),
                'end_time' => $date->copy()->setTime(13, 0),
            ];
        }
        DB::table('rests')->insert($rests);

        $base = Carbon::now()->startOfMonth();
        $startDate = $base->copy()->addMonth()->startOfMonth();
        $endDate = $base->copy()->addMonth()->endOfMonth();
        $rests = [];
        $userId = 1;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if($date->isWeekend()) {
                continue;
            }

            $work = DB::table('works')->where('user_id', $userId)
            ->whereDate('date', $date->format('Y-m-d'))
            ->first();

            $rests[] = [
                'work_id' => $work->id,
                'user_id' => $userId,
                'start_time' => $date->copy()->setTime(12, 0),
                'end_time' => $date->copy()->setTime(13, 0),
            ];
        }
        DB::table('rests')->insert($rests);
    }
}
