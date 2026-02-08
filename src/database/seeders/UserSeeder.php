<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザー
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 1,
            'email_verified_at' => now(),
        ]);

        // 一般ユーザー
        $users = [
            ['name' => '山田太郎', 'email' => 'user1@example.com'],
            ['name' => '鈴木花子', 'email' => 'user2@example.com'],
            ['name' => '佐藤次郎', 'email' => 'user3@example.com'],
            ['name' => '田中美咲', 'email' => 'user4@example.com'],
            ['name' => '高橋健太', 'email' => 'user5@example.com'],
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'role' => 0,
                'email_verified_at' => now(),
            ]);
        }
    }
}
