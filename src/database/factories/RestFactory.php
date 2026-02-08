<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'rest_start' => Carbon::today()->setTime(12, 0, 0),
            'rest_end' => Carbon::today()->setTime(13, 0, 0),
        ];
    }

    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'rest_start' => Carbon::now(),
            'rest_end' => null,
        ]);
    }
}
