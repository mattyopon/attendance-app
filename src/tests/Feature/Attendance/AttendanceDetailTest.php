<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
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
            'date' => Carbon::yesterday()->toDateString(),
            'clock_in' => Carbon::yesterday()->setTime(9, 0, 0),
            'clock_out' => Carbon::yesterday()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ], $attributes));
    }

    /**
     * 勤怠詳細が表示される
     */
    public function test_user_can_view_attendance_detail(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 他人の勤怠詳細にはアクセスできない（404）
     */
    public function test_user_cannot_view_other_users_attendance(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $attendance = $this->createAttendance($otherUser);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        // AttendanceController::show()で user_id チェック、firstOrFail()なので404
        $response->assertStatus(404);
    }

    /**
     * 勤怠詳細に出退勤時間が表示される
     */
    public function test_attendance_detail_shows_clock_times(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 勤怠詳細に休憩時間が表示される
     */
    public function test_attendance_detail_shows_rest_times(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::yesterday()->setTime(12, 0, 0),
            'rest_end' => Carbon::yesterday()->setTime(13, 0, 0),
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
