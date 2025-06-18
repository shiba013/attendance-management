<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\Rest;
use App\Models\WorkRequest;
use App\Models\WorkRequestTime;

class WorkRequestTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = WorkRequestTime::class;

    public function definition()
    {
        $status = $this->faker->numberBetween(0, 6);
        $base = Carbon::now();
        switch($status)
        {
            case 0:
                $before = null;
                $after = null;
                break;
            case 1:
                $before = $base->copy()->setTime(9, 0);
                $after = $base->copy()->setTime(10, 0);
                break;
            case 2:
                $before = $base->copy()->setTime(18, 0);
                $after = $base->copy()->setTime(19, 0);
                break;
            case 3:
                $before = $base->copy()->setTime(12, 0);
                $after = $base->copy()->setTime(13, 0);
                break;
            case 4:
                $before = $base->copy()->setTime(13, 0);
                $after = $base->copy()->setTime(14, 0);
                break;
            case 5:
                $before = null;
                $after = $base->copy()->setTime(16, 0);
                break;
            case 6:
                $before = null;
                $after = $base->copy()->setTime(17, 0);
                break;
        }
        return [
            'work_request_id' => WorkRequest::factory(),
            'rest_id' => Rest::factory(),
            'status' => $status,
            'before_time' => $before,
            'after_time' => $after,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
