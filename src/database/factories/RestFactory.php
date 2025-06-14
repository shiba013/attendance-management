<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\Work;
use App\Models\User;
use App\Models\Rest;

class RestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Rest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'work_id' => Work::factory(),
            'start_time' => Carbon::now()->setTime(12, 0),
            'end_time' => Carbon::now()->setTime(13, 0),
        ];
    }
}
