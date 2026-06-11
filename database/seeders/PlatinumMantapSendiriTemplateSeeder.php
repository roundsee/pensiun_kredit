<?php

namespace Database\Seeders;

use App\Models\ProductTemplate;
use App\Models\TemplateField;
use Illuminate\Database\Seeder;

class PlatinumMantapSendiriTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $template = ProductTemplate::firstOrCreate(
            ['template_name' => 'PLATINUM-MANTAP-Sendiri'],
            ['template_description' => 'Template default PLATINUM-MANTAP-Sendiri']
        );

        // Remove old rows to keep this seeder idempotent.
        $template->templateFields()->delete();

        $fields = [
            // Data Debitur
            ['section' => 'informasi_debitur', 'field_label' => 'Jenis Pensiun', 'field_name' => 'jenis_pensiun', 'field_type' => 'text'],
            ['section' => 'informasi_debitur', 'field_label' => 'Nama Debitur', 'field_name' => 'nama_debitur', 'field_type' => 'text'],
            ['section' => 'informasi_debitur', 'field_label' => 'Tanggal Lahir', 'field_name' => 'tanggal_lahir', 'field_type' => 'date'],
            ['section' => 'informasi_debitur', 'field_label' => 'Umur', 'field_name' => 'umur', 'field_type' => 'text'],
            ['section' => 'informasi_debitur', 'field_label' => 'Nomor Pensiun', 'field_name' => 'nomor_pensiun', 'field_type' => 'text'],
            ['section' => 'informasi_debitur', 'field_label' => 'Instansi', 'field_name' => 'instansi', 'field_type' => 'text'],
            ['section' => 'informasi_debitur', 'field_label' => 'Gaji Pensiun', 'field_name' => 'gaji_pensiun', 'field_type' => 'number'],
            ['section' => 'informasi_debitur', 'field_label' => 'Sisa Gaji saat pengajuan', 'field_name' => 'sisa_gaji_saat_pengajuan', 'field_type' => 'number'],

            // Data Pengajuan
            ['section' => 'data_pengajuan', 'field_label' => 'Produk', 'field_name' => 'produk', 'field_type' => 'text'],
            ['section' => 'data_pengajuan', 'field_label' => 'Mutasi', 'field_name' => 'mutasi', 'field_type' => 'text'],
            ['section' => 'data_pengajuan', 'field_label' => 'Bank Asal', 'field_name' => 'bank_asal', 'field_type' => 'text'],
            ['section' => 'data_pengajuan', 'field_label' => 'Bank Tujuan', 'field_name' => 'bank_tujuan', 'field_type' => 'text'],
            ['section' => 'data_pengajuan', 'field_label' => 'Tenor Max', 'field_name' => 'tenor_max', 'field_type' => 'number'],
            ['section' => 'data_pengajuan', 'field_label' => 'Plafond Max', 'field_name' => 'plafond_max', 'field_type' => 'number'],
            ['section' => 'data_pengajuan', 'field_label' => 'Tenor', 'field_name' => 'tenor', 'field_type' => 'number'],
            ['section' => 'data_pengajuan', 'field_label' => 'Nama Marketing', 'field_name' => 'nama_marketing', 'field_type' => 'text'],
            ['section' => 'data_pengajuan', 'field_label' => 'Kode Area', 'field_name' => 'kode_area', 'field_type' => 'text'],
            ['section' => 'data_pengajuan', 'field_label' => 'USIA LUNAS', 'field_name' => 'usia_lunas', 'field_type' => 'text'],
            ['section' => 'data_pengajuan', 'field_label' => 'TGL PERMOHONAN', 'field_name' => 'tgl_permohonan', 'field_type' => 'date'],
            ['section' => 'data_pengajuan', 'field_label' => 'TGL LUNAS', 'field_name' => 'tgl_lunas', 'field_type' => 'date'],
            ['section' => 'data_pengajuan', 'field_label' => 'Blokir Angsuran', 'field_name' => 'blokir_angsuran', 'field_type' => 'number'],

            // Data Financial
            ['section' => 'data_financial', 'field_label' => 'Plafond', 'field_name' => 'plafond', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'ANGSURAN', 'field_name' => 'angsuran', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'Biaya Adm Angs', 'field_name' => 'biaya_adm_angs', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'Total Angsuran', 'field_name' => 'total_angsuran', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'PROVISI', 'field_name' => 'provisi', 'field_type' => 'number', 'calculation_type' => 'percentage', 'default_value' => 1.5],
            ['section' => 'data_financial', 'field_label' => 'ADMINISTRASI', 'field_name' => 'administrasi', 'field_type' => 'number', 'calculation_type' => 'percentage', 'default_value' => 5.0],
            ['section' => 'data_financial', 'field_label' => 'ASURANSI', 'field_name' => 'asuransi', 'field_type' => 'number', 'calculation_type' => 'percentage', 'default_value' => 10.19],
            ['section' => 'data_financial', 'field_label' => 'Extra Premi', 'field_name' => 'extra_premi', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'BLOKIR ANGSURAN', 'field_name' => 'blokir_angsuran', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'PELUNASAN', 'field_name' => 'pelunasan', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'TATA LAKSANA', 'field_name' => 'tata_laksana', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'TOTAL BIAYA', 'field_name' => 'total_biaya', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'SISA GAJI AKHIR', 'field_name' => 'sisa_gaji_akhir', 'field_type' => 'number', 'calculation_type' => 'fixed'],
            ['section' => 'data_financial', 'field_label' => 'TERIMA BERSIH', 'field_name' => 'terima_bersih', 'field_type' => 'number', 'calculation_type' => 'fixed'],
        ];

        foreach ($fields as $order => $row) {
            TemplateField::create([
                'product_template_id' => $template->id,
                'field_name' => $row['field_name'],
                'field_label' => $row['field_label'],
                'field_type' => $row['field_type'] ?? 'text',
                'is_required' => false,
                'section' => $row['section'],
                'account_code' => $row['account_code'] ?? null,
                'calculation_type' => $row['calculation_type'] ?? null,
                'default_value' => $row['default_value'] ?? null,
                'field_order' => $order,
            ]);
        }
    }
}
