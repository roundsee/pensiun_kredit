<?php

namespace Tests\Unit;

use App\Services\KbSimulationExcelService;
use PHPUnit\Framework\TestCase;

class KbSimulationExcelServiceTest extends TestCase
{
    public function test_it_forces_five_when_bank_mantap_and_bank_asal_btpn_even_if_input_is_one(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BANK BTPN',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => 1,
        ]);

        $this->assertSame(5, $count);
    }

    public function test_it_forces_five_when_bank_mantap_and_bank_asal_is_in_special_list(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'resolveBlokirAngsuranCount');
        $method->setAccessible(true);

        $count = $method->invoke($service, [
            'bank_asal' => 'BANK BUKOPIN',
            'bank_tujuan' => 'MANTAP',
            'blokir_angsuran' => null,
        ]);

        $this->assertSame(5, $count);
    }
}
