<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChannelingAccountSeeder extends Seeder
{
    public function run(): void
    {
        $groupMap = [];
        foreach (['Piutang', 'Kewajiban', 'Pendapatan', 'Kas/Bank'] as $groupName) {
            $groupId = DB::table('account_groups')->where('name', $groupName)->value('id');
            if (!$groupId) {
                $groupId = DB::table('account_groups')->insertGetId([
                    'name' => $groupName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $groupMap[$groupName] = $groupId;
        }

        $accounts = [
            ['code' => '1204', 'name' => 'Piutang Plafond Nasabah', 'type' => 'asset', 'account_group_id' => $groupMap['Piutang']],
            ['code' => '1205', 'name' => 'Piutang Reimburse Pendana', 'type' => 'asset', 'account_group_id' => $groupMap['Piutang']],
            ['code' => '2104', 'name' => 'Hutang Pokok Pendana', 'type' => 'liability', 'account_group_id' => $groupMap['Kewajiban']],
            ['code' => '2105', 'name' => 'Hutang Bunga Pendana', 'type' => 'liability', 'account_group_id' => $groupMap['Kewajiban']],
            ['code' => '4104', 'name' => 'Pendapatan Sharing Bunga KSP', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan']],
        ];

        foreach ($accounts as $account) {
            DB::table('accounts')->updateOrInsert(
                ['code' => $account['code']],
                array_merge($account, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
