<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者作成ヘルパー
     */
    private function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 1,
            'email_verified_at' => now(),
        ], $attributes));
    }

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
     * 管理者が日次勤怠一覧を表示できる
     */
    public function test_admin_can_view_daily_attendance_list(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $today = Carbon::today()->toDateString();

        $this->createAttendance($user, [
            'date' => $today,
            'clock_in' => Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(18, 0, 0),
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $today);

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 管理者が日付を変更できる
     */
    public function test_admin_can_change_date(): void
    {
        $admin = $this->createAdmin();
        $today = Carbon::today()->toDateString();

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $today);

        $response->assertStatus(200);

        $prevDate = Carbon::yesterday()->toDateString();
        $nextDate = Carbon::tomorrow()->toDateString();

        $response->assertSee($prevDate);
        $response->assertSee($nextDate);
    }

    /**
     * 管理者が勤怠詳細を表示できる
     */
    public function test_admin_can_view_attendance_detail(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::yesterday()->setTime(12, 0, 0),
            'rest_end' => Carbon::yesterday()->setTime(13, 0, 0),
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /**
     * 管理者が勤怠を修正できる（DB反映）
     */
    public function test_admin_can_update_attendance(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)->put('/admin/attendance/' . $attendance->id, [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'reason' => '管理者による修正',
        ]);

        $response->assertRedirect(route('admin.attendance.show', $attendance->id));

        $attendance->refresh();
        $this->assertEquals('09:30', $attendance->clock_in->format('H:i'));
        $this->assertEquals('18:30', $attendance->clock_out->format('H:i'));
    }

    /**
     * 一般ユーザーは管理者勤怠画面にアクセスできない
     */
    public function test_regular_user_cannot_access_admin_attendance(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/admin/attendance/list');

        $response->assertRedirect('/admin/login');
    }
}
