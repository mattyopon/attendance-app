<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
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
     * 会員登録画面が正しく表示される
     */
    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('会員登録');
    }

    /**
     * 名前が未入力の場合バリデーションエラー「お名前を入力してください」
     */
    public function test_name_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);

        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    /**
     * メールアドレスが未入力の場合バリデーションエラー「メールアドレスを入力してください」
     */
    public function test_email_is_required(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
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
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    /**
     * パスワードが8文字未満の場合バリデーションエラー「パスワードは8文字以上で入力してください」
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors(['password']);

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    /**
     * パスワード確認が一致しない場合バリデーションエラー「パスワードと一致しません」
     */
    public function test_password_confirmation_must_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password']);

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

    /**
     * 正しいデータで会員登録が成功し、/attendance にリダイレクトされる
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'role' => 0,
        ]);

        $response->assertRedirect('/attendance');
    }

    /**
     * 重複するメールアドレスではバリデーションエラー
     */
    public function test_email_must_be_unique(): void
    {
        $this->createUser(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
