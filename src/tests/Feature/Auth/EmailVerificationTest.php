<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証ユーザー作成ヘルパー
     */
    private function createUnverifiedUser(array $attributes = []): User
    {
        return User::factory()->unverified()->create(array_merge([
            'role' => 0,
        ], $attributes));
    }

    /**
     * FN011: メール認証画面が正常に表示される
     */
    public function test_email_verification_screen_is_displayed(): void
    {
        $user = $this->createUnverifiedUser();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
    }

    /**
     * FN012: 未認証ユーザーが勤怠画面にアクセスするとメール認証画面にリダイレクトされる
     */
    public function test_unverified_user_is_redirected_to_email_verification(): void
    {
        $user = $this->createUnverifiedUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertRedirect('/email/verify');
    }

    /**
     * FN013: メール認証リンクで認証が完了する
     */
    public function test_email_can_be_verified(): void
    {
        $user = $this->createUnverifiedUser();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    /**
     * 認証済みユーザーは勤怠画面にアクセスできる
     */
    public function test_verified_user_can_access_attendance(): void
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
    }
}
