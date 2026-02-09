<?php

namespace Tests\Feature\StampCorrection;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampCorrectionRequestTest extends TestCase
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
     * 修正申請が成功しDBにレコードが作成される
     */
    public function test_user_can_submit_correction_request(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/correction', [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'reason' => '打刻忘れのため修正',
        ]);

        $response->assertRedirect(route('stamp_correction.list'));

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => '打刻忘れのため修正',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);
    }

    /**
     * 備考未入力でバリデーションエラー「備考を記入してください」
     */
    public function test_reason_is_required(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/correction', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason']);

        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('reason'));
    }

    /**
     * 不正な時間でバリデーションエラー「出勤時間もしくは退勤時間が不適切な値です」
     */
    public function test_invalid_clock_times_show_error(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        // 出勤 > 退勤の不正なケース
        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/correction', [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'reason' => 'テスト修正',
        ]);

        $response->assertSessionHasErrors(['clock_out']);

        $errors = session('errors');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', $errors->first('clock_out'));
    }

    /**
     * 承認待ちの修正申請がある場合は二重申請できない
     */
    public function test_user_cannot_submit_duplicate_pending_request(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        // 既存の承認待ち申請
        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => $attendance->date,
            'requested_clock_in' => Carbon::yesterday()->setTime(9, 30, 0),
            'requested_clock_out' => Carbon::yesterday()->setTime(18, 30, 0),
            'reason' => '既存の申請',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        // 二重申請を試みる
        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/correction', [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'reason' => '重複申請',
        ]);

        $this->assertDatabaseCount('stamp_correction_requests', 1);
    }

    /**
     * FN030: 不正な休憩時間でバリデーションエラー「休憩時間が不適切な値です」
     */
    public function test_invalid_rest_times_show_error(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/correction', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['rest_start' => '13:00', 'rest_end' => '12:00'],
            ],
            'reason' => 'テスト修正',
        ]);

        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals('休憩時間が不適切な値です', $errors->first('rests.0.rest_end'));
    }

    /**
     * 休憩終了が退勤時間を超える場合バリデーションエラー「休憩時間もしくは退勤時間が不適切な値です」
     */
    public function test_rest_end_exceeding_clock_out_shows_error(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/correction', [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'rests' => [
                ['rest_start' => '12:00', 'rest_end' => '19:00'],
            ],
            'reason' => 'テスト修正',
        ]);

        $response->assertSessionHasErrors();

        $errors = session('errors');
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', $errors->first('rests.0.rest_end'));
    }

    /**
     * 修正申請一覧が表示される
     */
    public function test_user_can_view_correction_list(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => $attendance->date,
            'requested_clock_in' => Carbon::yesterday()->setTime(9, 30, 0),
            'requested_clock_out' => Carbon::yesterday()->setTime(18, 30, 0),
            'reason' => '打刻修正テスト',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('打刻修正テスト');
    }

    /**
     * R44: 修正申請一覧の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_correction_list_detail_links_to_attendance_detail(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => $attendance->date,
            'requested_clock_in' => Carbon::yesterday()->setTime(9, 30, 0),
            'requested_clock_out' => Carbon::yesterday()->setTime(18, 30, 0),
            'reason' => '詳細リンクテスト',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee(route('attendance.show', $attendance->id));
    }

    /**
     * 修正申請一覧に承認待ち/承認済みタブがある
     */
    public function test_correction_list_has_tabs(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        // 承認待ち申請
        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_date' => $attendance->date,
            'requested_clock_in' => Carbon::yesterday()->setTime(9, 30, 0),
            'requested_clock_out' => Carbon::yesterday()->setTime(18, 30, 0),
            'reason' => '承認待ちの申請',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        // 承認待ちタブ
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('承認待ちの申請');

        // 承認済みタブ
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        $response->assertSee('承認済み');
        // 承認待ちの申請は承認済みタブには表示されない
        $response->assertDontSee('承認待ちの申請');
    }
}
