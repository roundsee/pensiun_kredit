<?php

namespace Tests\Feature;

use App\Http\Controllers\KbSimulationController;
use App\Services\KbSimulationExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KbSimulationStorePayloadFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_payload_to_existing_data_simulasi_columns(): void
    {
        $controller = new KbSimulationController(app(KbSimulationExcelService::class));
        $method = new \ReflectionMethod($controller, 'filterPersistPayloadForDataSimulasi');
        $method->setAccessible(true);

        $payload = [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'tanggal_simulasi' => '2026-06-01',
            'nama_debitur' => 'Budi',
            'nomor_hp' => '081234567890',
            'angsuran_lainnya' => 250000,
            'umur_text' => '42 thn',
            'client_side_calculation' => true,
            'unknown_field' => 'should-be-removed',
            'status' => 'trial',
        ];

        $filtered = $method->invoke($controller, $payload);

        $this->assertSame('Platinum', $filtered['produk']);
        $this->assertSame('Budi', $filtered['nama_debitur']);
        $this->assertSame('2026-06-01', $filtered['tgl_permohonan']);
        $this->assertSame(250000, $filtered['angsuran_lain']);
        $this->assertArrayNotHasKey('umur_text', $filtered);
        $this->assertArrayNotHasKey('client_side_calculation', $filtered);
        $this->assertArrayNotHasKey('unknown_field', $filtered);
        $this->assertSame('trial', $filtered['status']);
    }
}
