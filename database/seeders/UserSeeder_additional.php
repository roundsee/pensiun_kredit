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
                'name' => 'Operasional 1',
                'email' => 'ops1@nbp.com',
                'role' => User::ROLE_SUPERVISOR,
            ],
            [
                'name' => 'Operasional 2',
                'email' => 'ops2@nbp.com',
                'role' => User::ROLE_SUPERVISOR,
            ],
            [
                'name' => 'Operasional 3',
                'email' => 'ops3@nbp.com',
                'role' => User::ROLE_SUPERVISOR,
            ],
            
        ];
    }
}