<?php

namespace Tests\Unit;

use App\Models\ProductStruct;
use App\Services\KbSimulationExcelService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KbSimulationExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seed_inserts_default_insurance_rates_for_live_app(): void
    {
        Artisan::call('db:seed');

        $this->assertGreaterThan(0, DB::table('insurance_rates')->count());
    }

    public function test_it_allows_creating_insurance_rate_with_bank_and_usia_fields(): void
    {
        $rate = \App\Models\InsuranceRate::query()->create([
            'product' => 'Platinum',
            'bank_tujuan' => 'BRI',
            'tenor' => 60,
            'usia' => '55-60',
            'premium_per_million' => 235.31,
        ]);

        $this->assertSame('BRI', $rate->bank_tujuan);
        $this->assertSame('55-60', $rate->usia);
        $this->assertSame(60, $rate->tenor);
        $this->assertEqualsWithDelta(235.31, $rate->premium_per_million, 0.0001);
    }

    public function test_it_uses_distinct_kantor_bayar_for_bank_tujuan_options(): void
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

        ProductStruct::query()->create([
            'produk' => 'Regular',
            'kantor_bayar' => 'MANTAP',
            'plafond_min' => 1000000,
            'plafond_max' => 250000000,
            'tenor_max' => 120,
            'rate_percent' => 0.13,
            'provisi_percent' => 0.01,
            'usia_masuk_min' => 55,
            'usia_max' => 80,
            'admin_percent' => 0.04,
            'blokir_angsuran' => 1,
            'taspen' => 800000,
            'tata_laksana' => 1500000,
            'tata_laksana_plus_percent' => 0.01,
            'admin_angsuran_percent' => 0.09,
            'dbr_percent' => 0.90,
            'asabri' => 300000,
            'usia_masuk_max' => 80,
            'sort_order' => 2,
        ]);

        ProductStruct::query()->create([
            'produk' => 'Regular 2',
            'kantor_bayar' => 'KB',
            'plafond_min' => 1000000,
            'plafond_max' => 250000000,
            'tenor_max' => 120,
            'rate_percent' => 0.13,
            'provisi_percent' => 0.01,
            'usia_masuk_min' => 55,
            'usia_max' => 80,
            'admin_percent' => 0.04,
            'blokir_angsuran' => 1,
            'taspen' => 800000,
            'tata_laksana' => 1500000,
            'tata_laksana_plus_percent' => 0.01,
            'admin_angsuran_percent' => 0.09,
            'dbr_percent' => 0.90,
            'asabri' => 300000,
            'usia_masuk_max' => 80,
            'sort_order' => 3,
        ]);

        $options = (new KbSimulationExcelService())->getSelectOptions();

        $this->assertSame(['KB', 'MANTAP'], $options['bank_tujuan']);
    }

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

    public function test_it_includes_simpanan_pokok_in_total_biaya_for_kb_and_mantap(): void
    {
        $service = new KbSimulationExcelService();

        $baseKb = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
            'simpanan_pokok' => 0,
        ]);

        $resultKb = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
            'simpanan_pokok' => 100000,
        ]);

        $this->assertSame(100000.0, round((float) $resultKb['simpanan_pokok'], 2));
        $this->assertEqualsWithDelta(100000.0, (float) $resultKb['total_biaya'] - (float) $baseKb['total_biaya'], 0.01);

        $baseMantap = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'MANTAP',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
            'simpanan_pokok' => 0,
        ]);

        $resultMantap = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'MANTAP',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
            'simpanan_pokok' => 100000,
        ]);

        $this->assertSame(100000.0, round((float) $resultMantap['simpanan_pokok'], 2));
        $this->assertEqualsWithDelta(100000.0, (float) $resultMantap['total_biaya'] - (float) $baseMantap['total_biaya'], 0.01);
    }

    public function test_it_detects_max_age_exceeded_when_date_is_past_the_80th_birthday(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'isDatePastMaxAge');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($service, Carbon::parse('1945-12-31'), Carbon::parse('2026-01-01'), 80));
        $this->assertFalse($method->invoke($service, Carbon::parse('1945-12-31'), Carbon::parse('2025-12-31'), 80));
    }

    public function test_it_reduces_tenor_max_to_zero_when_borrower_is_past_80th_birthday(): void
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
        $method = new \ReflectionMethod($service, 'calculateTenorMaxFromProductStruct');
        $method->setAccessible(true);

        $tenorMax = $method->invoke($service, 'KB', 'Platinum', 'Sendiri', '1945-12-31', '2026-01-01');

        $this->assertSame(0, $tenorMax);
    }

    public function test_it_formats_age_text_with_years_and_months(): void
    {
        $service = new KbSimulationExcelService();
        $method = new \ReflectionMethod($service, 'buildAgeText');
        $method->setAccessible(true);

        [$text, $years] = $method->invoke($service, Carbon::parse('1945-12-31'), Carbon::parse('2021-03-31'));

        $this->assertSame('75 thn 3 bln', $text);
        $this->assertSame(75, $years);
    }

    public function test_it_sets_tenor_max_to_84_for_birthdate_25_09_1953(): void
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

        $result = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-09-22',
            'tanggal_lahir' => '1953-09-25',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 0,
            'tenor' => 85,
            'plafond' => 100000000,
        ]);

        $this->assertSame(84, (int) $result['tenor_max']);
    }

    public function test_it_returns_plafond_rekomendasi_alias_for_the_calculation_result(): void
    {
        $service = new KbSimulationExcelService();

        $result = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        $this->assertArrayHasKey('plafond_rekomendasi', $result);
        $this->assertSame((float) $result['plafond_max'], (float) $result['plafond_rekomendasi']);
    }

    public function test_it_clamps_tenor_to_product_struct_max_when_input_exceeds_limit(): void
    {
        ProductStruct::query()->create([
            'produk' => 'MANTAP-Platinum-Janda',
            'kantor_bayar' => 'MANTAP',
            'plafond_min' => 1000000,
            'plafond_max' => 300000000,
            'tenor_max' => 96,
            'rate_percent' => 0.16,
            'provisi_percent' => 0.01,
            'usia_masuk_min' => 55,
            'usia_max' => 80,
            'admin_percent' => 0.05,
            'blokir_angsuran' => 1,
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

        $result = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Janda',
            'bank_tujuan' => 'MANTAP',
            'tanggal_simulasi' => '2026-09-08',
            'tanggal_lahir' => '1990-01-01',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 0,
            'tenor' => 120,
            'plafond' => 100000000,
        ]);

        $this->assertSame(96, (int) $result['tenor_max']);
        $this->assertSame(96, (int) $result['tenor']);
    }

    public function test_it_uses_product_struct_flagging_values_for_instansi(): void
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

        $taspenResult = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'instansi' => 'TASPEN',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        $asabriResult = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'instansi' => 'ASABRI',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        $this->assertSame(930000.0, (float) $taspenResult['tata_laksana']);
        $this->assertSame(430000.0, (float) $asabriResult['tata_laksana']);
    }

    public function test_it_adds_data_maintenance_to_angsuran_when_defined_on_product_struct(): void
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
            'data_maintenance' => 50000,
            'usia_masuk_max' => 80,
            'sort_order' => 1,
        ]);

        $service = new KbSimulationExcelService();

        ProductStruct::query()->where('produk', 'Platinum')->update(['data_maintenance' => 0]);
        $baseResult = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        ProductStruct::query()->where('produk', 'Platinum')->update(['data_maintenance' => 50000]);
        $result = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        $this->assertSame(50000.0, (float) $result['data_maintenance']);
        $this->assertEqualsWithDelta(50000.0, (float) $result['angsuran'] - (float) $baseResult['angsuran'], 0.01);
        $this->assertEqualsWithDelta(55000.0, (float) $result['total_angsuran'] - (float) $baseResult['total_angsuran'], 0.01);
    }

    public function test_it_reduces_plafond_max_when_data_maintenance_is_present(): void
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
            'data_maintenance' => 0,
            'usia_masuk_max' => 80,
            'sort_order' => 1,
        ]);

        $service = new KbSimulationExcelService();

        $withoutMaintenance = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        ProductStruct::query()->where('produk', 'Platinum')->update(['data_maintenance' => 50000]);

        $withMaintenance = $service->calculate([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-27',
            'tanggal_lahir' => '1956-06-02',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 200000000,
        ]);

        $this->assertLessThan((float) $withoutMaintenance['plafond_max'], (float) $withMaintenance['plafond_max']);
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
