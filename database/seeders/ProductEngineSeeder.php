<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Product;
use App\Models\ProductField;
use App\Models\ProductFinancial;
use Illuminate\Database\Seeder;

class ProductEngineSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::query()->firstOrCreate(
            ['name' => 'Pinjaman Pensiun Reguler'],
            [
                'description' => 'Produk channeling berbasis konfigurasi dinamis',
                'is_active' => true,
            ]
        );

        $fields = [
            ['field_name' => 'tgl_lahir', 'field_label' => 'Tanggal Lahir', 'field_type' => 'date', 'group' => 'informasi_debitur'],
            ['field_name' => 'nama', 'field_label' => 'Nama Debitur', 'field_type' => 'text', 'group' => 'informasi_debitur'],
            ['field_name' => 'status_perkawinan', 'field_label' => 'Status Perkawinan', 'field_type' => 'dropdown', 'group' => 'informasi_debitur'],
            ['field_name' => 'instansi_pensiun', 'field_label' => 'Instansi Pensiun', 'field_type' => 'text', 'group' => 'informasi_debitur'],
            ['field_name' => 'nopen', 'field_label' => 'No. Pensiun', 'field_type' => 'text', 'group' => 'informasi_debitur'],
            ['field_name' => 'gaji_bersih', 'field_label' => 'Gaji Bersih', 'field_type' => 'number', 'group' => 'informasi_debitur'],
            ['field_name' => 'tenor', 'field_label' => 'Tenor', 'field_type' => 'number', 'group' => 'data_pengajuan'],
            ['field_name' => 'blokir', 'field_label' => 'Blokir', 'field_type' => 'number', 'group' => 'data_pengajuan'],
            ['field_name' => 'bank', 'field_label' => 'Bank', 'field_type' => 'dropdown', 'group' => 'data_pengajuan'],
        ];

        foreach ($fields as $field) {
            ProductField::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'field_name' => $field['field_name'],
                    'group' => $field['group'],
                ],
                [
                    'field_label' => $field['field_label'],
                    'field_type' => $field['field_type'],
                    'is_required' => true,
                ]
            );
        }

        $financialItems = [
            ['item_name' => 'administrasi', 'account_code' => '4102', 'calculation_type' => 'percentage', 'default_value' => 1.00, 'transaction_type' => 'credit', 'is_deducted_at_disbursement' => true],
            ['item_name' => 'provisi', 'account_code' => '4101', 'calculation_type' => 'percentage', 'default_value' => 1.50, 'transaction_type' => 'credit', 'is_deducted_at_disbursement' => true],
            ['item_name' => 'asuransi', 'account_code' => '5101', 'calculation_type' => 'percentage', 'default_value' => 0.75, 'transaction_type' => 'debit', 'is_deducted_at_disbursement' => true],
            ['item_name' => 'angsuran', 'account_code' => '6002', 'calculation_type' => 'fixed', 'default_value' => 0, 'transaction_type' => 'credit', 'is_deducted_at_disbursement' => false],
            ['item_name' => 'plafon', 'account_code' => '6001', 'calculation_type' => 'fixed', 'default_value' => 0, 'transaction_type' => 'debit', 'is_deducted_at_disbursement' => false],
            ['item_name' => 'admin_angsuran', 'account_code' => '6003', 'calculation_type' => 'fixed', 'default_value' => 0, 'transaction_type' => 'credit', 'is_deducted_at_disbursement' => false],
            ['item_name' => 'pelunasan', 'account_code' => '6010', 'calculation_type' => 'fixed', 'default_value' => 0, 'transaction_type' => 'credit', 'is_deducted_at_disbursement' => false],
            ['item_name' => 'tatalaksana', 'account_code' => '6011', 'calculation_type' => 'fixed', 'default_value' => 0, 'transaction_type' => 'debit', 'is_deducted_at_disbursement' => false],
        ];

        foreach ($financialItems as $item) {
            $accountId = Account::query()->where('code', $item['account_code'])->value('id');
            if (!$accountId) {
                continue;
            }

            ProductFinancial::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'item_name' => $item['item_name'],
                ],
                [
                    'account_id' => $accountId,
                    'calculation_type' => $item['calculation_type'],
                    'default_value' => $item['default_value'],
                    'transaction_type' => $item['transaction_type'],
                    'is_deducted_at_disbursement' => $item['is_deducted_at_disbursement'],
                    'is_included_in_simulation' => true,
                ]
            );
        }
    }
}
