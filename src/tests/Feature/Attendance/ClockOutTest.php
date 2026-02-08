<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
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
     * ユーザーが退勤打刻できる（clock_out記録、status=3）
     */
    public function test_user_can_clock_out(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(18, 0, 0));

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $response->assertRedirect(route('attendance.index'));

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out);
        $this->assertEquals(Attendance::STATUS_LEFT, $attendance->status);

        Carbon::setTestNow();
    }

    /**
     * 出勤前は退勤できない
     */
    public function test_user_cannot_clock_out_without_clock_in(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $response->assertRedirect(route('attendance.index'));
        $this->assertDatabaseCount('attendances', 0);
    }

    /**
     * 二重退勤はできない（既に退勤済みの場合）
     */
    public function test_user_cannot_clock_out_twice(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user, [
            'clock_out' => now()->setTime(18, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $response->assertRedirect(route('attendance.index'));

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_LEFT, $attendance->status);
    }

    /**
     * 退勤打刻後にステータスが退勤済（left）に変わる
     */
    public function test_attendance_status_changes_to_left_after_clock_out(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(18, 0, 0));

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $this->actingAs($user)->post('/attendance/clock-out');

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_LEFT, $attendance->status);

        Carbon::setTestNow();
    }
}
