<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('roles')->upsert([
            ['name' => 'Marketing', 'slug' => 'marketing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Support Bisnis', 'slug' => 'support_bisnis', 'created_at' => $now, 'updated_at' => $now],
        ], ['slug'], ['name', 'updated_at']);
    }
}