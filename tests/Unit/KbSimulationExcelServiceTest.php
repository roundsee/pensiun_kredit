<?php

namespace Tests\Unit;

use App\Services\KbSimulationExcelService;
use PHPUnit\Framework\TestCase;

class KbSimulationExcelServiceTest extends TestCase
{
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

        $this->assertSame(['KB-Platinum-Sendiri', 'Platinum-Sendiri'], $keys);
    }
}
