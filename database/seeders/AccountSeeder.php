<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil id group
        $groupMap = [];
        $groupNames = [
            'Pendapatan', 'Piutang', 'Kewajiban', 'Kas/Bank', 'Lain-lain', 'Biaya', 'Ekuitas', 'Aset Tetap', 'Persediaan', 'Modal', 'Hutang Usaha', 'Hutang Pajak', 'Pendapatan Lain', 'Biaya Gaji', 'Biaya Operasional'
        ];
        $groupRows = \DB::table('account_groups')->whereIn('name', $groupNames)->get();
        foreach ($groupRows as $g) {
            $groupMap[$g->name] = $g->id;
        }
        $accounts = [
            // Aset Lancar
            ['code' => '1101', 'name' => 'Kas Besar', 'type' => 'asset', 'account_group_id' => $groupMap['Kas/Bank'] ?? null],
            ['code' => '1102', 'name' => 'Bank', 'type' => 'asset', 'account_group_id' => $groupMap['Kas/Bank'] ?? null],
            ['code' => '1201', 'name' => 'Piutang Pokok', 'type' => 'asset', 'account_group_id' => $groupMap['Piutang'] ?? null],
            ['code' => '1202', 'name' => 'Piutang Bunga', 'type' => 'asset', 'account_group_id' => $groupMap['Piutang'] ?? null],
            ['code' => '1301', 'name' => 'Persediaan Barang', 'type' => 'asset', 'account_group_id' => $groupMap['Persediaan'] ?? null],
            // Aset Tetap
            ['code' => '1401', 'name' => 'Aset Tetap', 'type' => 'asset', 'account_group_id' => $groupMap['Aset Tetap'] ?? null],
            // Kewajiban
            ['code' => '2101', 'name' => 'Hutang Asuransi', 'type' => 'liability', 'account_group_id' => $groupMap['Kewajiban'] ?? null],
            ['code' => '2102', 'name' => 'Hutang Usaha', 'type' => 'liability', 'account_group_id' => $groupMap['Hutang Usaha'] ?? null],
            ['code' => '2103', 'name' => 'Hutang Pajak', 'type' => 'liability', 'account_group_id' => $groupMap['Hutang Pajak'] ?? null],
            // Ekuitas
            ['code' => '3101', 'name' => 'Modal Disetor', 'type' => 'equity', 'account_group_id' => $groupMap['Modal'] ?? null],
            ['code' => '3102', 'name' => 'Laba Ditahan', 'type' => 'equity', 'account_group_id' => $groupMap['Ekuitas'] ?? null],
            // Pendapatan
            ['code' => '4101', 'name' => 'Pendapatan Provisi', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan'] ?? null],
            ['code' => '4102', 'name' => 'Pendapatan Administrasi', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan'] ?? null],
            ['code' => '4103', 'name' => 'Pendapatan Lain-lain', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan Lain'] ?? null],
            // Biaya
            ['code' => '5101', 'name' => 'Biaya Administrasi', 'type' => 'expense', 'account_group_id' => $groupMap['Biaya'] ?? null],
            ['code' => '5102', 'name' => 'Biaya Gaji', 'type' => 'expense', 'account_group_id' => $groupMap['Biaya Gaji'] ?? null],
            ['code' => '5103', 'name' => 'Biaya Operasional', 'type' => 'expense', 'account_group_id' => $groupMap['Biaya Operasional'] ?? null],
        ];
        // Akun khusus kebutuhan product template
        $productTemplateAccounts = [
            ['code' => '6001', 'name' => 'Plafond', 'type' => 'asset', 'account_group_id' => $groupMap['Lain-lain'] ?? null],
            ['code' => '6002', 'name' => 'Angsuran', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan'] ?? null],
            ['code' => '6003', 'name' => 'Biaya Adm Angs', 'type' => 'expense', 'account_group_id' => $groupMap['Biaya'] ?? null],
            ['code' => '6004', 'name' => 'Total Angsuran', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan'] ?? null],
            ['code' => '6005', 'name' => 'Provisi', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan'] ?? null],
            ['code' => '6006', 'name' => 'Administrasi', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan'] ?? null],
            ['code' => '6007', 'name' => 'Asuransi', 'type' => 'expense', 'account_group_id' => $groupMap['Biaya'] ?? null],
            ['code' => '6008', 'name' => 'Extra Premi', 'type' => 'expense', 'account_group_id' => $groupMap['Biaya'] ?? null],
            ['code' => '6009', 'name' => 'Blokir Angsuran', 'type' => 'asset', 'account_group_id' => $groupMap['Lain-lain'] ?? null],
            ['code' => '6010', 'name' => 'Pelunasan', 'type' => 'income', 'account_group_id' => $groupMap['Pendapatan'] ?? null],
            ['code' => '6011', 'name' => 'Tata Laksana', 'type' => 'expense', 'account_group_id' => $groupMap['Biaya'] ?? null],
        ];
        foreach (array_merge($accounts, $productTemplateAccounts) as $acc) {
            \DB::table('accounts')->updateOrInsert([
                'code' => $acc['code']
            ], $acc);
        }
    }
}
