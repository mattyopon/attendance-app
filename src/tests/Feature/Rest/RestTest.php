<?php

namespace Tests\Feature\Rest;

use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestTest extends TestCase
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
     * ユーザーが休憩を開始できる（restsレコード作成、status=2）
     */
    public function test_user_can_start_rest(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(12, 0, 0));

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/rest-start');

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('rests', [
            'attendance_id' => $attendance->id,
        ]);

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_ON_BREAK, $attendance->status);

        Carbon::setTestNow();
    }

    /**
     * ユーザーが休憩を終了できる（rest_end記録、status=1に戻る）
     */
    public function test_user_can_end_rest(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(13, 0, 0));

        $user = $this->createUser();
        $attendance = $this->createAttendance($user, [
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        $rest = Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::today()->setTime(12, 0, 0),
        ]);

        $response = $this->actingAs($user)->post('/attendance/rest-end');

        $response->assertRedirect(route('attendance.index'));

        $rest->refresh();
        $this->assertNotNull($rest->rest_end);

        $attendance->refresh();
        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);

        Carbon::setTestNow();
    }

    /**
     * 複数回の休憩を取ることができる
     */
    public function test_user_can_take_multiple_rests(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(12, 0, 0));

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        // 1回目の休憩開始
        $this->actingAs($user)->post('/attendance/rest-start');

        Carbon::setTestNow(Carbon::today()->setTime(12, 30, 0));

        // 1回目の休憩終了
        $this->actingAs($user)->post('/attendance/rest-end');

        Carbon::setTestNow(Carbon::today()->setTime(15, 0, 0));

        // 2回目の休憩開始
        $this->actingAs($user)->post('/attendance/rest-start');

        Carbon::setTestNow(Carbon::today()->setTime(15, 15, 0));

        // 2回目の休憩終了
        $this->actingAs($user)->post('/attendance/rest-end');

        $rests = Rest::where('attendance_id', $attendance->id)->get();
        $this->assertCount(2, $rests);

        Carbon::setTestNow();
    }

    /**
     * 出勤していない場合は休憩を開始できない
     */
    public function test_user_cannot_start_rest_without_clock_in(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/attendance/rest-start');

        $response->assertRedirect(route('attendance.index'));
        $this->assertDatabaseCount('rests', 0);
    }

    /**
     * 既に休憩中の場合は休憩を開始できない
     */
    public function test_user_cannot_start_rest_when_already_on_break(): void
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user, [
            'status' => Attendance::STATUS_ON_BREAK,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::today()->setTime(12, 0, 0),
        ]);

        // status=ON_BREAKの場合、RestController::start()はSTATUS_WORKINGを要求するので無効
        $response = $this->actingAs($user)->post('/attendance/rest-start');

        $response->assertRedirect(route('attendance.index'));

        // 既存の1件のみ
        $this->assertDatabaseCount('rests', 1);
    }
}
