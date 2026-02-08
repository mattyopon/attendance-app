<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = Carbon::today()->subDays(rand(1, 30));

        return [
            'user_id' => User::factory(),
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0, 0),
            'clock_out' => $date->copy()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ];
    }

    public function working(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0, 0),
            'clock_out' => null,
            'status' => Attendance::STATUS_WORKING,
        ]);
    }

    public function onBreak(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0, 0),
            'clock_out' => null,
            'status' => Attendance::STATUS_ON_BREAK,
        ]);
    }

    public function left(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);
    }
}
