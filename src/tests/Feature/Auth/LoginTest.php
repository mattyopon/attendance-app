<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
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
     * ログイン画面が正しく表示される
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('ログイン');
    }

    /**
     * メールアドレスが未入力の場合バリデーションエラー「メールアドレスを入力してください」
     */
    public function test_email_is_required(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    /**
     * パスワードが未入力の場合バリデーションエラー「パスワードを入力してください」
     */
    public function test_password_is_required(): void
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    /**
     * 正しい資格情報でログインが成功し、認証状態になる
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/attendance');
    }

    /**
     * 不正な資格情報でログインが失敗し、エラー「ログイン情報が登録されていません」
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $this->createUser([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
        $this->assertGuest();
    }
}
