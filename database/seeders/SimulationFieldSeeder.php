<?php

namespace Database\Seeders;

use App\Models\SimulationField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimulationFieldSeeder extends Seeder
{
    public function run(): void
    {
        $batchId = (string) Str::uuid();
        $sourceFilename = 'sample-simulation-ocr.pdf';

        $rows = [
            ['produk', 'Produk', 'Platinum', 'header'],
            ['jenis_pensiun', 'Jenis Pensiun', 'Sendiri', 'header'],
            ['mutasi', 'Mutasi', 'NON MUTASI', 'header'],
            ['bank_asal', 'Bank Asal', 'BANK BUKOPIN', 'header'],
            ['bank_tujuan', 'Bank Tujuan', 'BANK BUKOPIN', 'header'],

            ['nama_debitur', 'Nama Debitur', 'SORYANTO', 'debitur'],
            ['tanggal_lahir', 'Tanggal Lahir', '19 Agustus 1956', 'debitur'],
            ['umur', 'Umur', '69thn 8bulan', 'debitur'],
            ['nomor_pensiun', 'Nomor Pensiun', '51006736400', 'debitur'],
            ['instansi', 'Instansi', 'TASPEN', 'debitur'],
            ['gaji_pensiun', 'Gaji Pensiun', 'Rp1,730,970', 'debitur'],
            ['sisa_gaji_saat_pengajuan', 'Sisa Gaji saat pengajuan', 'Rp1,730,970', 'debitur'],
            ['tenor_max', 'Tenor Max', '124', 'pengajuan'],
            ['plafond_max', 'Plafond Max', 'Rp89,923,173', 'pengajuan'],
            ['tenor', 'Tenor', '110', 'pengajuan'],
            ['plafond', 'Plafond', 'Rp95,143,000', 'pengajuan'],
            ['blokir_angsuran', 'Blokir Angsuran', '1', 'pengajuan'],
            ['angsuran', 'ANGSURAN', '1,549,911', 'pengajuan'],
            ['biaya_adm_angs', 'Biaya Adm Angs', '154,991', 'pengajuan'],
            ['total_angsuran', 'Total Angsuran', '1,704,903', 'pengajuan'],

            ['provisi', 'PROVISI', 'Rp951,430', 'rincian_pembiayaan'],
            ['administrasi', 'ADMINISTRASI', 'Rp4,757,150', 'rincian_pembiayaan'],
            ['asuransi', 'ASURANSI', 'Rp28,223,220', 'rincian_pembiayaan'],
            ['extra_premi', 'Extra Premi', null, 'rincian_pembiayaan'],
            ['blokir_angsuran_biaya', 'BLOKIR ANGSURAN', 'Rp1,704,903', 'rincian_pembiayaan'],
            ['pelunasan', 'PELUNASAN', null, 'rincian_pembiayaan'],
            ['tata_laksana', 'TATA LAKSANA', 'Rp896,000', 'rincian_pembiayaan'],

            ['nama_marketing', 'Nama Marketing', '-', 'informasi_tambahan'],
            ['kode_area', 'Kode Area', 'JAKARTA 1101 - JAKARTA', 'informasi_tambahan'],
            ['usia_lunas', 'USIA LUNAS', '78thn 10bulan', 'informasi_tambahan'],
            ['tgl_permohonan', 'TGL PERMOHONAN', '08 Mei 2026', 'informasi_tambahan'],
            ['tgl_lunas', 'TGL LUNAS', '08 Juli 2035', 'informasi_tambahan'],
            ['total_biaya', 'TOTAL BIAYA', 'Rp36,532,702', 'informasi_tambahan'],
            ['sisa_gaji_akhir', 'SISA GAJI AKHIR', 'Rp26,067', 'informasi_tambahan'],
            ['terima_bersih', 'TERIMA BERSIH', 'Rp58,610,298', 'informasi_tambahan'],
        ];

        foreach ($rows as $index => $row) {
            SimulationField::query()->create([
                'simulation_batch_id' => $batchId,
                'source_filename' => $sourceFilename,
                'field_key' => $row[0],
                'field_label' => $row[1],
                'field_value' => $row[2],
                'section' => $row[3],
                'line_order' => $index + 1,
                'raw_line' => trim(($row[1] ?? '') . ' ' . ($row[2] ?? '')),
                'extracted_at' => now(),
            ]);
        }
    }
}
