<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDateTimeTest extends TestCase
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
     * 勤怠打刻画面に現在日付が表示される
     * ビューではJavaScriptで日付を表示しているため、スクリプトの存在を検証する
     */
    public function test_attendance_page_shows_current_date(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        // JavaScriptで日付表示するためのDOM要素が存在することを確認
        $response->assertSee('id="current-date"', false);
        // updateDateTime関数が存在することを確認
        $response->assertSee('updateDateTime', false);
    }

    /**
     * 勤怠打刻画面に現在時刻が表示される
     * ビューではJavaScriptで時刻を表示しているため、スクリプトの存在を検証する
     */
    public function test_attendance_page_shows_current_time(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        // JavaScriptで時刻表示するためのDOM要素が存在することを確認
        $response->assertSee('id="current-time"', false);
        // setIntervalによるリアルタイム更新が設定されていることを確認
        $response->assertSee('setInterval', false);
    }
}
