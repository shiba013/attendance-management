<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\Work;
use App\Models\User;
use App\Models\WorkRequest;

class WorkRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = WorkRequest::class;

    public function definition()
    {
        return [
            'work_id' => Work::factory(),
            'user_id' => User::factory(),
            'remarks' => $this->faker->sentence(),
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'reviewed_by_user_id' => User::factory(),
                'reviewed_at' => Carbon::now(),
                'status' => 1,
            ];
        });
    }
}
