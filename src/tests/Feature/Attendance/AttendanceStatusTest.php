<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザー作成ヘルパー
     */
    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 0,
            'email_verified_at' => now(),
        ], $attributes));
    }

    /**
     * 勤怠レコード作成ヘルパー
     */
    private function createAttendance(User $user, array $attributes = []): Attendance
    {
        return Attendance::create(array_merge([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'status' => Attendance::STATUS_WORKING,
        ], $attributes));
    }

    /**
     * 出勤前（勤務外）は出勤ボタンが表示される
     */
    public function test_off_duty_status_shows_clock_in_button(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
        $response->assertSee('出勤');
    }

    /**
     * 勤務中は退勤ボタンと休憩入ボタンが表示される
     */
    public function test_working_status_shows_clock_out_and_break_buttons(): void
    {
        $user = $this->createUser();
        $this->createAttendance($user, [
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('退勤');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩中は休憩戻ボタンが表示される
     */
    public function test_on_break_status_shows_break_end_button(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user, [
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::today()->setTime(12, 0, 0),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    /**
     * 退勤済みは「お疲れ様でした。」が表示される
     */
    public function test_left_status_shows_message(): void
    {
        $user = $this->createUser();
        $this->createAttendance($user, [
            'clock_out' => now()->setTime(18, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');
    }
}
