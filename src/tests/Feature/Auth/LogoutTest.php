<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
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
     * 一般ユーザーがログアウトすると未認証状態になる
     */
    public function test_user_can_logout(): void
    {
        $user = $this->createUser();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
    }

    /**
     * 管理者がログアウトすると未認証状態になる
     */
    public function test_admin_can_logout(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin);
        $this->assertAuthenticated();

        $response = $this->post('/admin/logout');

        $this->assertGuest();
    }
}
