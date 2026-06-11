<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'Pendapatan',
            'Piutang',
            'Kewajiban',
            'Kas/Bank',
            'Lain-lain',
            'Biaya',
        ];
        foreach ($groups as $group) {
            DB::table('account_groups')->updateOrInsert([
                'name' => $group
            ]);
        }
    }
}
