<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicSimulationRouteTest extends TestCase
{
    public function test_public_simulation_page_is_accessible_without_login(): void
    {
        $response = $this->get('/simulasi');

        $response->assertOk();
    }

    public function test_kb_calculation_endpoint_is_accessible_without_login(): void
    {
        $response = $this->postJson('/simulasi-kb/calculate', [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_lahir' => '1990-01-01',
            'tanggal_simulasi' => '2026-08-26',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
            'blokir_angsuran' => 1,
            'pelunasan' => 0,
        ]);

        $this->assertNotSame(302, $response->status());
    }

    public function test_kb_save_endpoint_can_persist_simulation_data(): void
    {
        $response = $this->postJson('/simulasi-kb/store', [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'mutasi' => 'Non Mutasi',
            'bank_asal' => 'BANK BUKOPIN',
            'bank_tujuan' => 'KB',
            'keterangan' => '',
            'nama_debitur' => 'John Doe',
            'tanggal_simulasi' => '2026-08-26',
            'tanggal_lahir' => '1990-01-01',
            'nomor_pensiun' => '12345',
            'nomor_hp' => '081234567890',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'blokir_angsuran' => 1,
            'pelunasan' => 0,
            'tenor' => 60,
            'plafond' => 200000000,
            'nama_marketing' => '-',
            'kode_area' => '-',
            'angsuran' => 500000,
            'total_angsuran' => 500000,
            'plafond_max' => 250000000,
            'tenor_max' => 60,
            'sisa_gaji_akhir' => 2500000,
            'terima_bersih' => 3000000,
            'status' => 'trial',
        ]);

        $response->assertStatus(201);
    }
}
