<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
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
     * 認証済みユーザーが出勤打刻できる（DBにレコード作成、status=1）
     */
    public function test_authenticated_user_can_clock_in(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(9, 0, 0));

        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        Carbon::setTestNow();
    }

    /**
     * 同日に2回出勤打刻はできない
     */
    public function test_user_cannot_clock_in_twice_same_day(): void
    {
        $user = $this->createUser();

        $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect(route('attendance.index'));
        $this->assertDatabaseCount('attendances', 1);
    }

    /**
     * 未認証ユーザーは出勤打刻できずリダイレクトされる
     */
    public function test_unauthenticated_user_cannot_clock_in(): void
    {
        $response = $this->post('/attendance/clock-in');

        $response->assertRedirect('/login');
    }

    /**
     * 出勤打刻後にステータスが勤務中（working）に変わる
     */
    public function test_attendance_status_changes_to_working_after_clock_in(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(9, 0, 0));

        $user = $this->createUser();

        $this->actingAs($user)->post('/attendance/clock-in');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);

        Carbon::setTestNow();
    }
}
