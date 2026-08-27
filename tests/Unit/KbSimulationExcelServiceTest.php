<?php

namespace Tests\Unit;

use App\Models\ProductStruct;
use App\Services\KbSimulationExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KbSimulationExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_product_struct_when_bank_and_product_are_stored_separately(): void
    {
        ProductStruct::query()->create([
            'produk' => 'Platinum',
            'kantor_bayar' => 'KB',
            'plafond_min' => 1000000,
            'plafond_max' => 300000000,
            'tenor_max' => 120,
            'rate_percent' => 0.14,
            'provisi_percent' => 0.01,
            'usia_masuk_min' => 55,
            'usia_max' => 80,
            'admin_percent' => 0.05,
            'blokir_angsuran' => 2,
            'taspen' => 850000,
            'tata_laksana' => 1750000,
            'tata_laksana_plus_percent' => 0.01,
            'admin_angsuran_percent' => 0.10,
            'dbr_percent' => 0.90,
            'asabri' => 350000,
            'usia_masuk_max' => 80,
            'sort_order' => 1,
        ]);

        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'firstProductStructForKeys');
        $method->setAccessible(true);

        $struct = $method->invoke($service, ['KB-Platinum-Sendiri', 'Platinum-Sendiri']);

        $this->assertNotNull($struct);
        $this->assertSame('Platinum', $struct->produk);
        $this->assertSame('KB', $struct->kantor_bayar);
    }

    public function test_it_defaults_to_one_when_no_valid_blokir_selection_is_provided(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BANK BTPN',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => null,
        ]);

        $this->assertSame(1, $count);
    }

    public function test_it_keeps_manual_blokir_selection_for_special_bank_mantap(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BANK BTPN',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => 3,
        ]);

        $this->assertSame(3, $count);
    }

    public function test_it_keeps_manual_blokir_selection_for_special_bank_mantap_when_user_selects_two(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BTPN',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => 2,
        ]);

        $this->assertSame(2, $count);
    }

    public function test_it_keeps_manual_blokir_selection_for_mantap_when_bank_is_not_special(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BANK MANDIRI',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => 3,
        ]);

        $this->assertSame(3, $count);
    }

    public function test_it_defaults_to_one_when_mantap_non_special_bank_has_no_valid_selection(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BANK MANDIRI',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => null,
        ]);

        $this->assertSame(1, $count);
    }

    public function test_it_falls_back_to_plain_product_key_when_bank_prefix_is_not_present_in_product_struct(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveProductStructKeys');
        $method->setAccessible(true);

        $keys = $method->invoke($service, 'KB', 'Platinum', 'Sendiri');

        $this->assertSame(['KB-Platinum-Sendiri', 'Platinum-Sendiri', 'Platinum'], $keys);
    }
}
