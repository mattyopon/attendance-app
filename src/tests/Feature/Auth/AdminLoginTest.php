<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
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
     * 管理者ログイン画面が正しく表示される
     */
    public function test_admin_login_page_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('管理者ログイン');
    }

    /**
     * 管理者（role=1）が正しい資格情報でログインできる
     */
    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = $this->createAdmin([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.attendance.list'));
    }

    /**
     * 一般ユーザー（role=0）は管理者としてログインできず、エラー「ログイン情報が登録されていません」
     */
    public function test_regular_user_cannot_login_as_admin(): void
    {
        $this->createUser([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
        $this->assertGuest();
    }

    /**
     * 管理者ログインでメールアドレスが未入力の場合バリデーションエラー
     */
    public function test_admin_email_is_required(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    /**
     * 管理者ログインでパスワードが未入力の場合バリデーションエラー
     */
    public function test_admin_password_is_required(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }
}
