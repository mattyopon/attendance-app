<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
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
     * 自分の勤怠一覧が表示される
     */
    public function test_user_can_view_own_attendance_list(): void
    {
        $user = $this->createUser();
        $this->createAttendance($user, [
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 月別でフィルタリングされて表示される
     */
    public function test_attendance_list_shows_monthly_data(): void
    {
        $user = $this->createUser();

        // 当月の勤怠
        $this->createAttendance($user, [
            'date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        // 先月の勤怠
        $lastMonth = Carbon::now()->subMonth();
        $this->createAttendance($user, [
            'date' => $lastMonth->startOfMonth()->format('Y-m-d'),
            'clock_in' => $lastMonth->copy()->startOfMonth()->setTime(10, 0, 0),
            'clock_out' => $lastMonth->copy()->startOfMonth()->setTime(19, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        // 当月のみ表示されること
        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee('09:00');
    }

    /**
     * 前月に遷移できる
     */
    public function test_user_can_navigate_to_previous_month(): void
    {
        $user = $this->createUser();

        $currentMonth = Carbon::now()->format('Y-m');
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $response = $this->actingAs($user)->get('/attendance/list?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee($prevMonth);
        $response->assertSee('前月');
    }

    /**
     * 翌月に遷移できる
     */
    public function test_user_can_navigate_to_next_month(): void
    {
        $user = $this->createUser();

        $currentMonth = Carbon::now()->format('Y-m');
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($user)->get('/attendance/list?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee($nextMonth);
        $response->assertSee('翌月');
    }

    /**
     * 詳細リンクが表示される
     */
    public function test_attendance_list_shows_detail_link(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user, [
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee(route('attendance.show', $attendance->id));
    }

    /**
     * R25: 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_rest_time_is_displayed_in_attendance_list(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user, [
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::now()->setTime(12, 0, 0),
            'rest_end' => Carbon::now()->setTime(13, 0, 0),
        ]);

        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee('1:00');
    }

    /**
     * R27: 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_displayed_in_attendance_list(): void
    {
        $user = $this->createUser();
        $this->createAttendance($user, [
            'date' => Carbon::now()->format('Y-m-d'),
            'clock_in' => Carbon::now()->setTime(9, 0, 0),
            'clock_out' => Carbon::now()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        $currentMonth = Carbon::now()->format('Y-m');
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $currentMonth);

        $response->assertStatus(200);
        $response->assertSee('18:00');
    }
}
