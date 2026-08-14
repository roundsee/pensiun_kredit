<?php

namespace Tests\Unit;

use App\Services\KbSimulationExcelService;
use PHPUnit\Framework\TestCase;

class KbSimulationExcelServiceTest extends TestCase
{
    public function test_it_defaults_to_five_when_mantap_special_bank_has_no_valid_blokir_selection(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BANK BTPN',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => null,
        ]);

        $this->assertSame(5, $count);
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
}
