<?php

namespace Database\Seeders;

use App\Models\Lender;
use App\Models\Loan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        $nasabah = User::query()->firstOrCreate(
            ['email' => 'nasabah.demo@example.com'],
            ['name' => 'Nasabah Demo', 'password' => bcrypt('password')]
        );

        $lender = Lender::query()->first();
        if (!$lender) {
            return;
        }

        $product = Product::query()->first();

        Loan::query()->updateOrCreate(
            ['loan_number' => 'CHN-2026-0001'],
            [
                'product_id' => $product?->id,
                'nasabah_id' => $nasabah->id,
                'lender_id' => $lender->id,
                'amount_plafond' => 50000000,
                'interest_rate' => 12,
                'provision_fee' => 500000,
                'admin_fee' => 150000,
                'status' => 'active',
                'disbursed_at' => now()->toDateString(),
                'debtor_data' => [
                    'nama' => 'Nasabah Demo',
                    'tgl_lahir' => '1970-01-01',
                    'status_perkawinan' => 'kawin',
                    'instansi_pensiun' => 'Taspen',
                    'nopen' => 'NP-0001',
                    'gaji_bersih' => 7000000,
                ],
                'submission_data' => [
                    'tenor' => 60,
                    'blokir' => 1,
                    'bank' => 'Bank Demo',
                ],
                'financial_data' => [
                    'provisi' => ['value' => 500000],
                    'administrasi' => ['value' => 150000],
                ],
            ]
        );
    }
}
