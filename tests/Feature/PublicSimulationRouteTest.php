<?php

namespace Tests\Feature;

use App\Models\ProductStruct;
use Tests\TestCase;

class PublicSimulationRouteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ProductStruct::query()->updateOrCreate(
            ['produk' => 'KB-Platinum-Sendiri'],
            [
                'tenor_max' => 144,
                'usia_max' => 80,
                'rate_percent' => '0.160000',
                'dbr_percent' => '0.900000',
                'admin_angsuran_percent' => '0.100000',
                'provisi_percent' => '0.010000',
                'admin_percent' => '0.020000',
                'blokir_angsuran' => 1,
                'sort_order' => 1,
            ]
        );
    }

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
            'tanggal_lahir' => '1956-06-02',
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
            'tanggal_lahir' => '1956-06-02',
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

    public function test_mobile_calculation_api_does_not_redirect_to_html(): void
    {
        $response = $this->post('/api/mobile/kb-simulasi/calculate', [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'mutasi' => 'Non Mutasi',
            'bank_asal' => 'BANK BUKOPIN',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
            'nama_marketing' => '-',
            'kode_area' => '101 - BANDUNG',
            'blokir_angsuran' => 1,
            'pelunasan' => 0,
        ], ['Accept' => 'application/json']);

        $this->assertNotSame(302, $response->status());
        $this->assertNotSame('text/html', $response->headers->get('Content-Type'));
    }

    public function test_mobile_calculation_api_returns_error_code_in_json(): void
    {
        $response = $this->postJson('/api/mobile/kb-simulasi/calculate', [
            'produk' => 'Platinum',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('code', 'VALIDATION_ERROR');
        $response->assertJsonStructure(['message', 'code', 'errors']);
    }

    public function test_mobile_tenor_max_api_is_available(): void
    {
        $response = $this->postJson('/api/mobile/kb-simulasi/tenor-max', [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
        ]);

        $response->assertOk();
        $response->assertJsonPath('code', 'TENOR_MAX_OK');
        $response->assertJsonPath('data.tenor_max', 118);
    }

    public function test_mobile_plafond_max_api_is_available(): void
    {
        $response = $this->postJson('/api/mobile/kb-simulasi/plafond-max', [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
        ]);

        $response->assertOk();
        $response->assertJsonPath('code', 'PLAFOND_MAX_OK');
        $response->assertJsonPath('data.plafond_max', 126168871.28362805);
    }

    public function test_mobile_preview_api_is_available(): void
    {
        $response = $this->postJson('/api/mobile/kb-simulasi/preview', [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'mutasi' => 'Non Mutasi',
            'bank_asal' => 'BANK BUKOPIN',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
            'blokir_angsuran' => 1,
            'pelunasan' => 0,
        ]);

        $response->assertOk();
        $response->assertJsonPath('code', 'CALCULATION_OK');
        $response->assertJsonStructure(['message', 'code', 'data', 'display', 'limits']);
    }
}
