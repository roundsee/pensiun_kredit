<?php

namespace App\Services;

use App\Models\KbReferenceOption;
use App\Models\ProductStruct;
use App\Models\TemplateField;
use App\Models\InsuranceRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class KbSimulationExcelService
{
    public function getSelectOptions(): array
    {
        $bankTujuan = KbReferenceOption::query()
            ->where('category', 'bank_tujuan')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('value')
            ->all();
$bankAsal = KbReferenceOption::query()
            ->where('category', 'bank_asal')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('value')
            ->all();
        $area = KbReferenceOption::query()
            ->where('category', 'area')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('value')
            ->all();

        return [
            'produk' => ['Platinum', 'Regular'],
            'jenis_pensiun' => ['Sendiri', 'Janda', 'Duda'],
            'bank_asal' => $bankAsal,
            'bank_tujuan' => $bankTujuan,
            'area' => $area,
        ];
    }

    public function calculate(array $input): array
    {
        $input = array_merge([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'BANK BUKOPIN',
            'nama_debitur' => '-',
            'tanggal_simulasi' => now()->toDateString(),
            'tanggal_lahir' => now()->toDateString(),
            'nomor_pensiun' => '-',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 0,
            'angsuran_lainnya' => 0,
            'tenor' => null,
            'plafond' => null,
            'nama_marketing' => '-',
            'kode_area' => '-',
        ], $input);

        //$ioFactoryClass = 'PhpOffice\\PhpSpreadsheet\\IOFactory';
        Log::info('Starting calculation without spreadsheet...');
        return $this->calculateWithoutSpreadsheet($input);
        // if (!class_exists($ioFactoryClass)) {
        //     return $this->calculateWithoutSpreadsheet($input);
        // }

        // $reader = $ioFactoryClass::createReader('Xlsx');
        // if (method_exists($reader, 'setReadDataOnly')) {
        //     $reader->setReadDataOnly(true);
        // }

        // $spreadsheet = $reader->load(storage_path('upload/Simulasi_KB.xlsx'));
        // $sheet = $spreadsheet->getSheetByName('SIMULASI');

        // if ($sheet === null) {
        //     $spreadsheet->disconnectWorksheets();
        //     throw new \RuntimeException('Sheet SIMULASI tidak ditemukan.');
        // }

        // $excelDateClass = 'PhpOffice\\PhpSpreadsheet\\Shared\\Date';
        // $tanggalSimulasi = Carbon::parse($input['tanggal_simulasi']);
        // $tanggalSimulasiExcel = class_exists($excelDateClass)
        //     ? $excelDateClass::PHPToExcel($tanggalSimulasi)
        //     : $tanggalSimulasi->format('d/m/Y');
        // $tanggalLahir = Carbon::parse($input['tanggal_lahir']);
        // $tanggalLahirExcel = class_exists($excelDateClass)
        //     ? $excelDateClass::PHPToExcel($tanggalLahir)
        //     : $tanggalLahir->format('d/m/Y');

        // $sheet->setCellValue('E10', (string) $input['produk']);
        // $sheet->setCellValue('E11', (string) $input['jenis_pensiun']);
        // $sheet->setCellValue('E14', (string) $input['bank_tujuan']);
        // $sheet->setCellValue('E17', $tanggalSimulasiExcel);
        // $sheet->setCellValue('E18', (string) $input['nama_debitur']);
        // $sheet->setCellValue('E19', $tanggalLahirExcel);
        // $sheet->setCellValue('E21', (string) $input['nomor_pensiun']);
        // $sheet->setCellValue('E22', (string) $input['instansi']);
        // $sheet->setCellValue('E23', (float) $input['gaji_pensiun']);
        // $sheet->setCellValue('E24', (float) ($input['angsuran_lainnya'] ?? 0));
        // if ($input['tenor'] !== null && $input['tenor'] !== '') {
        //     $sheet->setCellValue('E28', (int) $input['tenor']);
        // }
        // if ($input['plafond'] !== null && $input['plafond'] !== '') {
        //     $sheet->setCellValue('E29', (float) $input['plafond']);
        // }
        // if (($input['rate_percent_override'] ?? null) !== null && ($input['rate_percent_override'] ?? '') !== '') {
        //     $sheet->setCellValue('E26A', (float) $input['rate_percent_override']);
        // }
        // if (($input['admin_angsuran_percent_override'] ?? null) !== null && ($input['admin_angsuran_percent_override'] ?? '') !== '') {
        //     $sheet->setCellValue('E26B', (float) $input['admin_angsuran_percent_override']);
        // }
        // $sheet->setCellValue('E43', (string) $input['nama_marketing']);
        // $sheet->setCellValue('E44', (string) $input['kode_area']);

        // $tenorMaxFromStruct = $this->calculateTenorMaxFromProductStruct(
        //     (string) $input['produk'],
        //     (string) $input['jenis_pensiun'],
        //     (string) $input['tanggal_lahir'],
        //     (string) $input['tanggal_simulasi']
        // );

        // $tenorMaxCalculated = (int) round((float) $this->cellCalculated($sheet, 'E26'));
        // $tenorMaxFinal = $tenorMaxFromStruct ?? $tenorMaxCalculated;

        // $angsuranLainnya = (float) ($input['angsuran_lainnya'] ?? 0);
        // $gajiPensiun = (float) ($input['gaji_pensiun'] ?? 0);
        // $sisaGajiSaatPengajuan = max(0.0, $gajiPensiun - $angsuranLainnya);
        // $tenorInput = ($input['tenor'] === null || $input['tenor'] === '') ? $tenorMaxFinal : (int) $input['tenor'];

        // $plafondMaxFromStruct = $this->calculatePlafondMaxFromProductStruct(
        //     (string) $input['produk'],
        //     (string) $input['jenis_pensiun'],
        //     $tenorInput,
        //     $sisaGajiSaatPengajuan,
        //     ($input['rate_percent_override'] ?? null),
        //     ($input['admin_angsuran_percent_override'] ?? null)
        // );

        // $plafondMaxCalculated = (float) $this->cellCalculated($sheet, 'E27');
        // $plafondMaxFinal = $plafondMaxFromStruct ?? $plafondMaxCalculated;

        // $result = [
        //     'produk' => (string) $input['produk'],
        //     'jenis_pensiun' => (string) $input['jenis_pensiun'],
        //     'mutasi' => 'NON MUTASI',
        //     'bank_tujuan' => (string) $input['bank_tujuan'],
        //     'nama_debitur' => (string) $input['nama_debitur'],
        //     'tanggal_simulasi' => Carbon::parse($input['tanggal_simulasi'])->toDateString(),
        //     'tanggal_lahir' => Carbon::parse($input['tanggal_lahir'])->toDateString(),
        //     'nomor_pensiun' => (string) $input['nomor_pensiun'],
        //     'instansi' => (string) $input['instansi'],
        //     'gaji_pensiun' => $gajiPensiun,
        //     'angsuran_lainnya' => $angsuranLainnya,
        //     'tenor' => ($input['tenor'] === null || $input['tenor'] === '') ? 0 : (int) $input['tenor'],
        //     'plafond' => ($input['plafond'] === null || $input['plafond'] === '') ? 0 : (float) $input['plafond'],
        //     'nama_marketing' => (string) $input['nama_marketing'],
        //     'kode_area' => (string) $input['kode_area'],
        //     'umur_text' => (string) $this->cellCalculated($sheet, 'E20'),
        //     'umur' => $this->parseAgeYear($this->cellCalculated($sheet, 'E20')),
        //     'tenor_max' => $tenorMaxFinal,
        //     'plafond_max' => $plafondMaxFinal,
        //     'angsuran' => (float) $this->cellCalculated($sheet, 'E31'),
        //     'biaya_adm_angs' => (float) $this->cellCalculated($sheet, 'E32'),
        //     'total_angsuran' => (float) $this->cellCalculated($sheet, 'E33'),
        //     'provisi' => (float) $this->cellCalculated($sheet, 'E35'),
        //     'administrasi' => (float) $this->cellCalculated($sheet, 'E36'),
        //     'asuransi' => (float) $this->cellCalculated($sheet, 'E37'),
        //     'pelunasan' => (float) $this->cellCalculated($sheet, 'E39'),
        //     'amount_blokir_angsuran' => (float) $this->cellCalculated($sheet, 'E41'),
        //     'blokir_angsuran' => (float) $this->cellCalculated($sheet, 'E41'),
        //     'total_biaya' => (float) $this->cellCalculated($sheet, 'E51'),
        //     'sisa_gaji_saat_pengajuan' => $sisaGajiSaatPengajuan,
        //     'sisa_gaji_akhir' => (float) $this->cellCalculated($sheet, 'E52'),
        //     'extra_premi' => 0.0,
        //     'tata_laksana' => '',
        //     'terima_bersih' => (float) $this->cellCalculated($sheet, 'E54'),
        //     'usia_lunas_text' => (string) $this->cellCalculated($sheet, 'E46'),
        //     'usia_lunas' => $this->parseAgeYear($this->cellCalculated($sheet, 'E46')),
        //     'tgl_permohonan' => $this->cellDate($sheet, 'E47'),
        //     'tgl_lunas' => $this->cellDate($sheet, 'E49'),
        // ];

        // $spreadsheet->disconnectWorksheets();

        // return $result;
    }

    private function calculateWithoutSpreadsheet(array $input): array
    {
       // $productKey = trim((string) $input['produk'] . '-' . (string) $input['jenis_pensiun']);
       $productKey = trim((string) $input['bank_tujuan'] . '-' . (string) $input['produk'] . '-' . (string) $input['jenis_pensiun']);
        $struct = ProductStruct::query()
            ->where('produk', $productKey)
            ->first();

        $tanggalSimulasi = Carbon::parse($input['tanggal_simulasi']);
        $tanggalLahir = Carbon::parse($input['tanggal_lahir']);

        [$umurText, $umurTahun] = $this->buildAgeText($tanggalLahir, $tanggalSimulasi);

        $tenorMax = $this->calculateTenorMaxFromProductStruct(
            (string) $input['bank_tujuan'],
            (string) $input['produk'],
            (string) $input['jenis_pensiun'],
            (string) $input['tanggal_lahir'],
            (string) $input['tanggal_simulasi']
        ) ?? 0;

        $angsuranLainnya = (float) ($input['angsuran_lainnya'] ?? 0);
        $gajiPensiun = (float) ($input['gaji_pensiun'] ?? 0);
        $sisaGajiSaatPengajuan = max(0.0, $gajiPensiun - $angsuranLainnya);

        $tenor = ($input['tenor'] === null || $input['tenor'] === '') ? 0 : (int) $input['tenor'];
        $tenorForPlafond = $tenor > 0 ? $tenor : $tenorMax;
        $plafond = ($input['plafond'] === null || $input['plafond'] === '') ? 0.0 : (float) $input['plafond'];

        $plafondMax = $this->calculatePlafondMaxFromProductStruct(
            
           (string) $input['bank_tujuan'],
            (string) $input['produk'],
            (string) $input['jenis_pensiun'],
            $tenorForPlafond,
            $sisaGajiSaatPengajuan,
            $input['rate_percent_override'] ?? null,
            $input['admin_angsuran_percent_override'] ?? null
        ) ?? 0.0;

        $rateSource = ($input['rate_percent_override'] ?? null) !== null && ($input['rate_percent_override'] ?? '') !== ''
            ? (float) $input['rate_percent_override']
            : (float) ($struct?->rate_percent ?? 0);
        $adminAngsuranSource = ($input['admin_angsuran_percent_override'] ?? null) !== null && ($input['admin_angsuran_percent_override'] ?? '') !== ''
            ? (float) $input['admin_angsuran_percent_override']
            : (float) ($struct?->admin_angsuran_percent ?? 0);

        $rateTahunan = $this->normalizePercent($rateSource);
        $monthlyRate = $rateTahunan / 12;
        $adminAngsuranPercent = $this->normalizePercent($adminAngsuranSource);
        $provisiPercent = $this->normalizePercent((float) ($struct?->provisi_percent ?? 0));
        $administrasiPercent = $this->normalizePercent((float) ($struct?->admin_percent ?? 0));
        Log::Info("BANK TUJUAN : " . (string) $input['bank_tujuan']);
        $asuransiPercent = $this->resolveInsurancePercent((string) $input['bank_tujuan'],(string) $input['produk'], $tenor,$umurTahun); 
        Log::Info("Resolved insurance percent: {$asuransiPercent} for bank_tujuan={$input['bank_tujuan']}, product={$input['produk']}, tenor={$tenor}, usia={$umurTahun}");
        $angsuran = $tenor > 0 && $plafond > 0
            ? abs($this->excelPmt($monthlyRate, $tenor, $plafond)) + 10000.0
            : 0.0;
        $biayaAdmAngs = $angsuran * $adminAngsuranPercent;
        $totalAngsuran = $angsuran + $biayaAdmAngs;
        
        $provisi = $plafond * $provisiPercent;
        Log::info("Provisi calculated: {$provisi}");
        $administrasi = $plafond * $administrasiPercent;
        $asuransi = $plafond * $asuransiPercent;
        $extraPremi = 0.0;
        $blokirAngsuranCount = max(1, min(3, (int) ($input['blokir_angsuran'] ?? 1)));
        $amountBlokirAngsuran = $blokirAngsuranCount * $totalAngsuran;
        $pelunasan = max(0.0, (float) ($input['pelunasan'] ?? 0));

        $instansi = strtolower(trim((string) ($input['instansi'] ?? '')));
        $flagging = $instansi === 'taspen' ? 816000.0 : ($instansi === 'asabri' ? 350000.0 : 0.0);
        $materai = 80000.0;
        $tataLaksana = $flagging + $materai;

        $totalBiaya = $provisi + $administrasi + $asuransi + $extraPremi + $amountBlokirAngsuran + $tataLaksana + $pelunasan;
        $sisaGajiAkhir = $sisaGajiSaatPengajuan - $totalAngsuran;
        $terimaBersih = $plafond - $totalBiaya;

        $tglLunas = null;
        $usiaLunasText = '-';
        $usiaLunas = null;
        if ($tenor > 0) {
            $tanggalLunas = $tanggalSimulasi->copy()->addMonths($tenor);
            $tglLunas = $tanggalLunas->toDateString();
            [$usiaLunasText, $usiaLunas] = $this->buildAgeText($tanggalLahir, $tanggalLunas);
        }

        return [
            'produk' => (string) $input['produk'],
            'jenis_pensiun' => (string) $input['jenis_pensiun'],
            'mutasi' => strtoupper((string) ($input['mutasi'] ?? 'NON MUTASI')),
            'bank_tujuan' => (string) $input['bank_tujuan'],
            'nama_debitur' => (string) $input['nama_debitur'],
            'tanggal_simulasi' => $tanggalSimulasi->toDateString(),
            'tanggal_lahir' => $tanggalLahir->toDateString(),
            'nomor_pensiun' => (string) $input['nomor_pensiun'],
            'instansi' => (string) $input['instansi'],
            'gaji_pensiun' => $gajiPensiun,
            'angsuran_lainnya' => $angsuranLainnya,
            'tenor' => $tenor,
            'plafond' => $plafond,
            'nama_marketing' => (string) $input['nama_marketing'],
            'kode_area' => (string) $input['kode_area'],
            'umur_text' => $umurText,
            'umur' => $umurTahun,
            'tenor_max' => $tenorMax,
            'plafond_max' => $plafondMax,
            'angsuran' => $angsuran,
            'biaya_adm_angs' => $biayaAdmAngs,
            'total_angsuran' => $totalAngsuran,
            'provisi' => $provisi,
            'administrasi' => $administrasi,
            'asuransi' => $asuransi,
            'pelunasan' => $pelunasan,
            'amount_blokir_angsuran' => $amountBlokirAngsuran,
            'blokir_angsuran' => $blokirAngsuranCount,
            'total_biaya' => $totalBiaya,
            'sisa_gaji_saat_pengajuan' => $sisaGajiSaatPengajuan,
            'sisa_gaji_akhir' => $sisaGajiAkhir,
            'extra_premi' => $extraPremi,
            'tata_laksana' => $tataLaksana,
            'terima_bersih' => $terimaBersih,
            'usia_lunas_text' => $usiaLunasText,
            'usia_lunas' => $usiaLunas,
            'tgl_permohonan' => $tanggalSimulasi->toDateString(),
            'tgl_lunas' => $tglLunas,
        ];
    }

    private function calculateTenorMaxFromProductStruct(string $bank_tujuan,string $produk, string $jenisPensiun, string $tanggalLahir, string $tanggalSimulasi): ?int
    {
       // $productKey = trim($produk . '-' . $jenisPensiun);
        $productKey = trim($bank_tujuan . '-' . $produk . '-' . $jenisPensiun);
        $struct = ProductStruct::query()
            ->where('produk', $productKey)
            ->first();

        if ($struct === null) {
            return null;
        }

        $usiaMaxTahun = (int) ($struct->usia_max ?? 0);
        $tenorMaxProduk = (int) ($struct->tenor_max ?? 0);

        if ($usiaMaxTahun <= 0 || $tenorMaxProduk <= 0) {
            return null;
        }

        $tanggalAcuan = Carbon::parse($tanggalSimulasi);
        $tanggalLahirCarbon = Carbon::parse($tanggalLahir);

        // Samakan formula dengan frontend (Blade JS):
        // month diff = year diff * 12 + month diff, lalu -1 jika day acuan < day lahir.
        $usiaDebiturBulan = (($tanggalAcuan->year - $tanggalLahirCarbon->year) * 12)
            + ($tanggalAcuan->month - $tanggalLahirCarbon->month);

        if ($tanggalAcuan->day < $tanggalLahirCarbon->day) {
            $usiaDebiturBulan -= 1;
        }

        $usiaDebiturBulan = max(0, $usiaDebiturBulan);
        $usiaMaxBulan = $usiaMaxTahun * 12;
        $sisaMasaBulan = max(0, $usiaMaxBulan - $usiaDebiturBulan);

        return max(0, min($sisaMasaBulan, $tenorMaxProduk));
    }

    private function calculatePlafondMaxFromProductStruct(
        
    string $bank_tujuan,
        string $produk,
        string $jenisPensiun,
        int $tenor,
        float $sisaGajiSaatPengajuan,
        mixed $ratePercentOverride = null,
        mixed $adminAngsuranPercentOverride = null
    ): ?float {
        $productKey = trim($bank_tujuan . '-' . $produk . '-' . $jenisPensiun);

        $struct = ProductStruct::query()
            ->where('produk', $productKey)
            ->first();

        if ($struct === null || $tenor <= 0 || $sisaGajiSaatPengajuan <= 0) {
            return null;
        }

        $rateSource = ($ratePercentOverride !== null && $ratePercentOverride !== '')
            ? (float) $ratePercentOverride
            : (float) ($struct->rate_percent ?? 0);

        $adminAngsuranSource = ($adminAngsuranPercentOverride !== null && $adminAngsuranPercentOverride !== '')
            ? (float) $adminAngsuranPercentOverride
            : (float) ($struct->admin_angsuran_percent ?? 0);

        $rateTahunan = $this->normalizePercent($rateSource);
        $ratioGajiMax = $this->normalizePercent((float) ($struct->dbr_percent ?? 0));
        $adminAngsuran = $this->normalizePercent($adminAngsuranSource);

        if ($rateTahunan <= 0 || $ratioGajiMax <= 0) {
            return null;
        }

        // Excel formula reference:
        // =PV(C21/12, E29, -MIN(E25*C20, (E25-120000-(10000*D35*10))/(1+D35)))
        $monthlyRate = $rateTahunan / 12;
        $kandidatPertama = $sisaGajiSaatPengajuan * $ratioGajiMax;
        $kandidatKedua = ($sisaGajiSaatPengajuan - 120000 - (10000 * $adminAngsuran * 10)) / (1 + $adminAngsuran);
        $basisAngsuran = min($kandidatPertama, $kandidatKedua);

        if ($basisAngsuran <= 0) {
            return 0.0;
        }

        $pv = $this->excelPv($monthlyRate, $tenor, -$basisAngsuran);

        return max(0.0, $pv);
    }

    private function normalizePercent(float $value): float
    {
        if ($value > 1) {
            return $value / 100;
        }

        return $value;
    }

    private function resolveInsurancePercent(?string $bank_tujuan = null,?string $product = null, ?int $tenor = null,?int $usia = null): float
    {
        Log::info("Resolving insurance percent for bank_tujuan={$bank_tujuan}, product={$product}, tenor={$tenor}, usia={$usia}");
        $rate = null;
        // If product+tenor provided, prefer explicit table lookup for per-million premium.
        if ($product !== null && $tenor !== null && $bank_tujuan !== null) {
            // Excel MATCH(..., -1) on a descending sorted array finds the smallest
            // value greater than or equal to the lookup; implement equivalent logic:
            // find the smallest tenor >= requested tenor. If none, fallback to the
            // largest available tenor.

            Log::info("Looking up insurance rate for bank_tujuan={$bank_tujuan}, product={$product}, tenor={$tenor}, usia={$usia}");
            if($bank_tujuan=="KB"){
                if($product=="Platinum") {
                $rate = InsuranceRate::query()
                    ->where('product', $product)
                    ->where('bank_tujuan', $bank_tujuan)
                    ->where('tenor', '>=', $tenor)
                    ->orderBy('tenor', 'asc')
                    ->first();
                }
                if($product=="Regular") {
                $rate = InsuranceRate::query()
                    ->where('product', $product)
                    ->where('bank_tujuan', $bank_tujuan)
                    ->where('usia', '>=', $usia)
                    ->where('tenor', '>=', $tenor)
                    ->orderBy('tenor', 'asc')
                    ->first();
                }
            }
            if($bank_tujuan=="MANTAP"){
                $rate = InsuranceRate::query()
                    ->where('product', $product)
                    ->where('bank_tujuan', $bank_tujuan)
                    ->where('tenor', '>=', $tenor)
                    ->orderBy('tenor', 'asc')
                    ->first();
            }
            if($bank_tujuan=="POS"){
                $rate = InsuranceRate::query()
                    ->where('product', $product)
                    ->where('bank_tujuan', $bank_tujuan)
                    ->where('tenor', '>=', $tenor)
                    ->orderBy('tenor', 'asc')
                    ->first();
            }

            if ($rate === null) {
                // no tenor >= requested, pick the largest available tenor instead
                $rate = InsuranceRate::query()
                    ->where('product', $product)
                    ->orderBy('tenor', 'desc')
                    ->first();
            }

            if ($rate !== null) {
                // premium_per_million is given like Excel table values (e.g. 235.31).
                // Excel logic used INDEX(...)/10 to get 23.531 (%) which corresponds
                // to 0.23531 proportion. To reproduce that: divide by 1000.
                return (float) $rate->premium_per_million / 1000.0;
            }
        }
        Log::Info($rate);
        $defaultValue = TemplateField::query()
            ->where('field_name', 'asuransi')
            ->orderByDesc('updated_at')
            ->value('default_value');
        Log::info("Using default insurance percent: {$defaultValue}");
        return $this->normalizePercent((float) ($defaultValue ?? 0));
    }

    private function buildAgeText(Carbon $tanggalLahir, Carbon $tanggalAcuan): array
        {// Menggunakan diff native php melalui Carbon untuk mendapatkan pecahannya
        $diff = $tanggalLahir->diff($tanggalAcuan);
        
        $years = $diff->y;
        $remainingMonths = $diff->m;
        $days = $diff->d;

        // LOGIKA ANDA: Jika ada kelebihan hari (> 0), bulatkan ke atas menjadi +1 bulan
        if ($days > 0) {
            $remainingMonths += 1;
        }

        // Jika bulan genap atau lebih dari 12 setelah dibulatkan
        if ($remainingMonths >= 12) {
            $years += 1;
            $remainingMonths = 0;
        }

        return [sprintf('%d thn %d bln', $years, $remainingMonths), $years];
    }

    private function excelPmt(float $rate, int $numberOfPeriods, float $presentValue, float $futureValue = 0.0, int $type = 0): float
    {
        if ($numberOfPeriods <= 0) {
            return 0.0;
        }

        if (abs($rate) < 1e-12) {
            return -($presentValue + $futureValue) / $numberOfPeriods;
        }

        $factor = pow(1 + $rate, $numberOfPeriods);

        return -($rate * ($futureValue + $presentValue * $factor)) / ((1 + $rate * $type) * ($factor - 1));
    }

    private function excelPv(float $rate, int $numberOfPeriods, float $payment, float $futureValue = 0.0, int $type = 0): float
    {
        if ($numberOfPeriods <= 0) {
            return 0.0;
        }

        if (abs($rate) < 1e-12) {
            return -($futureValue + $payment * $numberOfPeriods);
        }

        $factor = pow(1 + $rate, $numberOfPeriods);

        return -($futureValue + $payment * (1 + $rate * $type) * (($factor - 1) / $rate)) / $factor;
    }

    private function cellCalculated(mixed $sheet, string $cellRef): mixed
    {
        return $sheet->getCell($cellRef)->getCalculatedValue();
    }

    private function cellDate(mixed $sheet, string $cellRef): ?string
    {
        $value = $sheet->getCell($cellRef)->getCalculatedValue();

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $excelBase = Carbon::create(1899, 12, 30, 0, 0, 0)->startOfDay();
            return $excelBase->copy()->addDays((int) $value)->toDateString();
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    private function parseAgeYear(mixed $value): ?int
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (preg_match('/(\d+)\s*thn/i', $text, $match)) {
            return (int) $match[1];
        }

        return null;
    }

}
