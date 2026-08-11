<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roleIds = Role::query()
            ->whereIn('slug', [
                User::ROLE_MARKETING,
                User::ROLE_SUPPORT_BISNIS,
                User::ROLE_OPERATION,
                User::ROLE_ADMIN,
                User::ROLE_SUPERVISOR,
            ])
            ->pluck('id', 'slug');

        $generatedCredentials = [];

        foreach ($this->defaultUsers() as $defaultUser) {
            $user = User::query()->firstOrNew([
                'email' => $defaultUser['email'],
            ]);

            $user->name = $defaultUser['name'];
            $user->role_id = $roleIds[$defaultUser['role']] ?? null;

            if (! $user->exists) {
                $plainPassword = Str::random(12);
                $user->password = Hash::make($plainPassword);
                $generatedCredentials[] = [
                    'email' => $defaultUser['email'],
                    'password' => $plainPassword,
                ];
            }

            $user->save();
        }

        if ($this->command && $generatedCredentials !== []) {
            $this->command->warn('Password acak untuk user baru (simpan baik-baik):');
            foreach ($generatedCredentials as $credential) {
                $this->command->line(sprintf('%s | %s', $credential['email'], $credential['password']));
            }
        }
    }

    /**
     * @return array<int, array{name: string, email: string, role: string}>
     */
    private function defaultUsers(): array
    {
        return [
            [
                'name' => 'Admin',
                'email' => 'admin@nbp.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Marketing',
                'email' => 'marketing@nbp.com',
                'role' => User::ROLE_MARKETING,
            ],
            [
                'name' => 'Support Bisnis',
                'email' => 'support.bisnis@nbp.com',
                'role' => User::ROLE_SUPPORT_BISNIS,
            ],
            [
                'name' => 'Operation',
                'email' => 'operation@nbp.com',
                'role' => User::ROLE_OPERATION,
            ],
        ];
    }
}