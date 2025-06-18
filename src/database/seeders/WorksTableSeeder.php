<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class WorksTableSeeder extends Seeder
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
        $userIds = [1, 2, 3];

        foreach($userIds as $userId) {
            $works = [];
            for($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if($date->isWeekend()) {
                    continue;
                }
                $works[] = [
                    'user_id' => $userId,
                    'date' => $date->copy()->format('Y-m-d'),
                    'start_time' => $date->copy()->setTime(9, 0),
                    'end_time' => $date->copy()->setTime(18, 0),
                    'status' => 2,
                ];
            }
            DB::table('works')->insert($works);
        }
    }
}