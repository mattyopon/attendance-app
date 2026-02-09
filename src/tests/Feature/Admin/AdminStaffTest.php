<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
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
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ], $attributes));
    }

    /**
     * 管理者がスタッフ一覧を表示できる
     */
    public function test_admin_can_view_staff_list(): void
    {
        $admin = $this->createAdmin();
        $users = [];
        for ($i = 0; $i < 3; $i++) {
            $users[] = $this->createUser();
        }

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }

    /**
     * 管理者がスタッフ別の勤怠一覧を表示できる
     */
    public function test_admin_can_view_staff_attendance_list(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $this->createAttendance($user);

        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 管理者がスタッフ勤怠の月を変更できる
     */
    public function test_admin_can_navigate_months(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $currentMonth = Carbon::now()->format('Y-m');
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee($prevMonth);
        $response->assertSee($nextMonth);
    }

    /**
     * 管理者がCSVをエクスポートできる（Content-Typeがtext/csv）
     */
    public function test_admin_can_export_csv(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser(['name' => 'テスト太郎']);
        $this->createAttendance($user);

        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '/export?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    /**
     * R58: 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_staff_attendance_detail_link(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee(route('admin.attendance.show', $attendance->id));
    }

    /**
     * 一般ユーザーはスタッフ一覧にアクセスできない
     */
    public function test_regular_user_cannot_access_staff_list(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/admin/staff/list');

        $response->assertRedirect('/admin/login');
    }
}
