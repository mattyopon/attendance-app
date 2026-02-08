<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザー（role=0）を取得
        $users = User::where('role', 0)->get();

        foreach ($users as $user) {
            $this->createAttendanceForUser($user);
        }
    }

    private function createAttendanceForUser(User $user): void
    {
        $today = Carbon::today();

        for ($i = 1; $i <= 30; $i++) {
            $date = $today->copy()->subDays($i);

            // 土日は除外
            if ($date->isWeekend()) {
                continue;
            }

            // 出勤時刻: 9:00 +/- 30分ランダム
            $clockInMinutes = rand(-30, 30);
            $clockIn = $date->copy()->setTime(9, 0, 0)->addMinutes($clockInMinutes);

            // 退勤時刻: 18:00 +/- 30分ランダム
            $clockOutMinutes = rand(-30, 30);
            $clockOut = $date->copy()->setTime(18, 0, 0)->addMinutes($clockOutMinutes);

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'status' => Attendance::STATUS_LEFT,
            ]);

            // 休憩: 12:00前後 ~ 13:00前後
            $restStartMinutes = rand(-10, 10);
            $restEndMinutes = rand(-10, 10);
            $restStart = $date->copy()->setTime(12, 0, 0)->addMinutes($restStartMinutes);
            $restEnd = $date->copy()->setTime(13, 0, 0)->addMinutes($restEndMinutes);

            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_start' => $restStart,
                'rest_end' => $restEnd,
            ]);
        }
    }
}
