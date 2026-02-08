<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStampCorrectionTest extends TestCase
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
     * 修正申請レコード作成ヘルパー
     */
    private function createCorrectionRequest(User $user, int $status = StampCorrectionRequest::STATUS_PENDING): StampCorrectionRequest
    {
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday()->toDateString(),
            'clock_in' => Carbon::yesterday()->setTime(9, 0, 0),
            'clock_out' => Carbon::yesterday()->setTime(18, 0, 0),
            'status' => Attendance::STATUS_LEFT,
        ]);

        return StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => $attendance->date,
            'requested_clock_in' => Carbon::yesterday()->setTime(9, 30, 0),
            'requested_clock_out' => Carbon::yesterday()->setTime(18, 30, 0),
            'reason' => 'テスト修正申請',
            'status' => $status,
        ]);
    }

    /**
     * 管理者が修正申請一覧を表示できる
     */
    public function test_admin_can_view_correction_list(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $this->createCorrectionRequest($user);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('テスト修正申請');
    }

    /**
     * 管理者が修正申請を承認できる（申請status=1になる）
     */
    public function test_admin_can_approve_correction(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $correction = $this->createCorrectionRequest($user);

        $response = $this->actingAs($admin)->put('/admin/stamp_correction_request/' . $correction->id . '/approve');

        $response->assertRedirect(route('admin.stamp_correction.list'));

        $correction->refresh();
        $this->assertEquals(StampCorrectionRequest::STATUS_APPROVED, $correction->status);
        $this->assertEquals($admin->id, $correction->approved_by);
    }

    /**
     * 承認後に勤怠データが正しく反映される
     */
    public function test_approve_updates_attendance_data(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $correction = $this->createCorrectionRequest($user);
        $attendanceId = $correction->attendance_id;

        $this->actingAs($admin)->put('/admin/stamp_correction_request/' . $correction->id . '/approve');

        $attendance = Attendance::find($attendanceId);
        $this->assertEquals('09:30', $attendance->clock_in->format('H:i'));
        $this->assertEquals('18:30', $attendance->clock_out->format('H:i'));
    }

    /**
     * 一般ユーザーは修正申請を承認できない
     */
    public function test_regular_user_cannot_approve_correction(): void
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();
        $correction = $this->createCorrectionRequest($anotherUser);

        $response = $this->actingAs($user)->put('/admin/stamp_correction_request/' . $correction->id . '/approve');

        $response->assertRedirect('/admin/login');

        // ステータスは変わっていないこと
        $correction->refresh();
        $this->assertEquals(StampCorrectionRequest::STATUS_PENDING, $correction->status);
    }
}
