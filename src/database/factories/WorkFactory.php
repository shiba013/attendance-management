<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\Work;
use App\Models\User;

class WorkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Work::class;

    public function definition()
    {
        $base = Carbon::now();
        return [
            'user_id' => User::factory(),
            'date' => $base->copy()->format('Y-m-d'),
            'start_time' => $base->copy()->setTime(9, 0),
            'end_time' => $base->copy()->setTime(18, 0),
            'status' => $this->faker->randomElement([1, 2, 3]),
        ];
    }
}
