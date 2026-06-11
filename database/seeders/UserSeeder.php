<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roleIds = Role::query()
            ->whereIn('slug', [
                User::ROLE_MARKETING,
                User::ROLE_SUPERVISOR,
                User::ROLE_SUPPORT_BISNIS,
            ])
            ->pluck('id', 'slug');

        foreach ($this->defaultUsers() as $defaultUser) {
            User::query()->updateOrCreate([
                'email' => $defaultUser['email'],
            ], [
                'name' => $defaultUser['name'],
                'role_id' => $roleIds[$defaultUser['role']] ?? null,
                // password: "password"
                'password' => bcrypt('password'),
            ]);
        }
    }

    /**
     * @return array<int, array{name: string, email: string, role: string}>
     */
    private function defaultUsers(): array
    {
        return [
            [
                'name' => 'Test Marketing',
                'email' => 'test@example.com',
                'role' => User::ROLE_MARKETING,
            ],
            [
                'name' => 'Test Supervisor',
                'email' => 'supervisor@example.com',
                'role' => User::ROLE_SUPERVISOR,
            ],
            [
                'name' => 'Test Support Bisnis',
                'email' => 'support.bisnis@example.com',
                'role' => User::ROLE_SUPPORT_BISNIS,
            ],
        ];
    }
}