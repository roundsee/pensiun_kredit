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
        $bankTujuan = ProductStruct::query()
            ->whereNotNull('kantor_bayar')
            ->where('kantor_bayar', '!=', '')
            ->select('kantor_bayar')
            ->distinct()
            ->orderBy('kantor_bayar')
            ->pluck('kantor_bayar')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        if ($bankTujuan === []) {
            $bankTujuan = KbReferenceOption::query()
                ->where('category', 'bank_tujuan')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('value')
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();
        }

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
            'simpanan_pokok' => 0,
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
        $productKeys = $this->resolveProductStructKeys(
            (string) ($input['bank_tujuan'] ?? ''),
            (string) ($input['produk'] ?? ''),
            (string) ($input['jenis_pensiun'] ?? '')
        );
        $struct = $this->firstProductStructForKeys($productKeys);

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
        $simpananPokok = (float) ($input['simpanan_pokok'] ?? 0);
        $gajiPensiun = (float) ($input['gaji_pensiun'] ?? 0);
        $sisaGajiSaatPengajuan = max(0.0, $gajiPensiun - $angsuranLainnya);

        $tenor = ($input['tenor'] === null || $input['tenor'] === '') ? 0 : (int) $input['tenor'];
        if ($tenorMax > 0) {
            $tenor = max(0, min($tenor, $tenorMax));
        }
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
        $dataMaintenance = (float) ($struct?->data_maintenance ?? 0.0);
        $angsuran = $tenor > 0 && $plafond > 0
            ? abs($this->excelPmt($monthlyRate, $tenor, $plafond)) + 10000.0 + $dataMaintenance
            : 0.0;
        $biayaAdmAngs = $angsuran * $adminAngsuranPercent;
        $totalAngsuran = $angsuran + $biayaAdmAngs;
        
        $provisi = $plafond * $provisiPercent;
        Log::info("Provisi calculated: {$provisi}");
        $administrasi = $plafond * $administrasiPercent;
        $asuransi = $plafond * $asuransiPercent;
        $extraPremi = 0.0;
        $blokirAngsuranCount = $this->resolveBlokirAngsuranCount($input);
        $amountBlokirAngsuran = $blokirAngsuranCount * $totalAngsuran;
        $pelunasan = max(0.0, (float) ($input['pelunasan'] ?? 0));

        $instansi = strtolower(trim((string) ($input['instansi'] ?? '')));
        $flagging = 0.0;

        if ($instansi === 'taspen') {
            $flagging = (float) ($struct?->taspen ?? 0.0);
        } elseif ($instansi === 'asabri') {
            $flagging = (float) ($struct?->asabri ?? 0.0);
        }

        $materai = 80000.0;
        $tataLaksana = $flagging + $materai;

        $totalBiaya = $provisi + $administrasi + $asuransi + $extraPremi + $amountBlokirAngsuran + $tataLaksana + $pelunasan + $simpananPokok;
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
            'simpanan_pokok' => $simpananPokok,
            'tenor' => $tenor,
            'plafond' => $plafond,
            'nama_marketing' => (string) $input['nama_marketing'],
            'kode_area' => (string) $input['kode_area'],
            'umur_text' => $umurText,
            'umur' => $umurTahun,
            'tenor_max' => $tenorMax,
            'plafond_max' => $plafondMax,
            'plafond_rekomendasi' => $plafondMax,
            'angsuran' => $angsuran,
            'biaya_adm_angs' => $biayaAdmAngs,
            'total_angsuran' => $totalAngsuran,
            'provisi' => $provisi,
            'administrasi' => $administrasi,
            'asuransi' => $asuransi,
            'data_maintenance' => $dataMaintenance,
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
        $productKeys = $this->resolveProductStructKeys($bank_tujuan, $produk, $jenisPensiun);
        $struct = $this->firstProductStructForKeys($productKeys);

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
        $maxAgeBirthday = $tanggalLahirCarbon->copy()->addYears($usiaMaxTahun);

        if ($tanggalAcuan->greaterThanOrEqualTo($maxAgeBirthday)) {
            return 0;
        }

        $sisaMasaBulan = (($maxAgeBirthday->year - $tanggalAcuan->year) * 12)
            + ($maxAgeBirthday->month - $tanggalAcuan->month);

        if ($maxAgeBirthday->day < $tanggalAcuan->day) {
            $sisaMasaBulan -= 1;
        }

        $sisaMasaBulan = max(0, $sisaMasaBulan);

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
        $productKeys = $this->resolveProductStructKeys($bank_tujuan, $produk, $jenisPensiun);
        $struct = $this->firstProductStructForKeys($productKeys);

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
        $adminPenalty = 10000.0 * $adminAngsuran * 5.0;
        $kandidatKedua = ($sisaGajiSaatPengajuan - 120000.0 - $adminPenalty) / (1 + $adminAngsuran);
        $basisAngsuran = min($kandidatPertama, $kandidatKedua);
        $dataMaintenance = (float) ($struct->data_maintenance ?? 0.0);
        $basisAngsuranNetto = max(0.0, $basisAngsuran - $dataMaintenance);

        if ($basisAngsuranNetto <= 0) {
            return 0.0;
        }

        $pv = $this->excelPv($monthlyRate, $tenor, -$basisAngsuranNetto);

        return max(0.0, $pv);
    }

    private function resolveProductStructKeys(string $bankTujuan, string $produk, string $jenisPensiun): array
    {
        $candidates = [];
        $pieces = [
            trim((string) $bankTujuan),
            trim((string) $produk),
            trim((string) $jenisPensiun),
        ];

        $prefixedKey = trim(implode('-', array_filter($pieces, fn ($piece) => $piece !== '')));
        if ($prefixedKey !== '') {
            $candidates[] = $prefixedKey;
        }

        $plainKey = trim(implode('-', array_filter([
            trim((string) $produk),
            trim((string) $jenisPensiun),
        ], fn ($piece) => $piece !== '')));
        if ($plainKey !== '' && !in_array($plainKey, $candidates, true)) {
            $candidates[] = $plainKey;
        }

        $productOnly = trim((string) $produk);
        if ($productOnly !== '' && !in_array($productOnly, $candidates, true)) {
            $candidates[] = $productOnly;
        }

        return array_values(array_unique($candidates));
    }

    private function firstProductStructForKeys(array $productKeys): ?ProductStruct
    {
        foreach ($productKeys as $key) {
            $struct = ProductStruct::query()->where('produk', $key)->first();
            if ($struct !== null) {
                return $struct;
            }
        }

        foreach ($productKeys as $key) {
            $keyParts = array_values(array_filter(array_map('trim', preg_split('/[-_\/\s]+/', $key) ?: []), fn ($part) => $part !== ''));
            if (count($keyParts) < 2) {
                continue;
            }

            $product = $keyParts[0] ?? null;
            $jenis = $keyParts[1] ?? null;
            if ($product === null || $product === '') {
                continue;
            }

            $fallback = ProductStruct::query()
                ->where(function ($query) use ($product, $jenis) {
                    $query->where('produk', $product)
                        ->orWhere('produk', $product . ($jenis !== null && $jenis !== '' ? '-' . $jenis : ''));
                })
                ->first();

            if ($fallback !== null) {
                return $fallback;
            }
        }

        return null;
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

        $defaultValue = TemplateField::query()
            ->where('field_name', 'asuransi')
            ->orderByDesc('updated_at')
            ->value('default_value');

        if ($product === null || $product === '') {
            Log::info("Using default insurance percent: {$defaultValue}");
            return $this->normalizePercent((float) ($defaultValue ?? 0));
        }

        $bankValue = trim((string) ($bank_tujuan ?? ''));
        $rate = $this->findBestInsuranceRate($product, $bankValue, $tenor, $usia);

        if ($rate !== null) {
            Log::info("Resolved insurance rate for bank_tujuan={$bank_tujuan}, product={$product}, tenor={$tenor}, usia={$usia}: " . $rate->premium_per_million);
            return (float) $rate->premium_per_million / 1000.0;
        }

        Log::info("No insurance rate found for bank_tujuan={$bank_tujuan}, product={$product}, tenor={$tenor}, usia={$usia}. Using fallback rate: " . ($defaultValue ?? 'null'));
        return $this->normalizePercent((float) ($defaultValue ?? 0));
    }

    private function findBestInsuranceRate(string $product, string $bank_tujuan, ?int $tenor, ?int $usia): ?InsuranceRate
    {
        $normalizedProduct = strtolower(trim($product));
        if ($normalizedProduct === '') {
            return null;
        }

        $rows = InsuranceRate::query()
            ->whereRaw('LOWER(TRIM(product)) = ?', [$normalizedProduct])
            ->get();

        $bankKey = $this->normalizeInsuranceBank($bank_tujuan);
        $bankRows = $rows->filter(function (InsuranceRate $row) use ($bankKey) {
            return $bankKey !== '' && $this->normalizeInsuranceBank($row->bank_tujuan) === $bankKey;
        });
        $genericRows = $rows->filter(function (InsuranceRate $row) {
            return $this->normalizeInsuranceBank($row->bank_tujuan) === '';
        });

        $candidatePools = [];
        if ($bankRows->isNotEmpty()) {
            $candidatePools[] = $bankRows;
        }
        if ($genericRows->isNotEmpty()) {
            $candidatePools[] = $genericRows;
        }

        $fallbackPools = [];
        foreach ($candidatePools as $candidatePool) {
            $rate = $this->selectInsuranceRateFromRows($candidatePool, $product, $tenor, $usia);
            if ($rate !== null) {
                return $rate;
            }
            $fallbackPools[] = $candidatePool;
        }

        foreach ($fallbackPools as $candidatePool) {
            $rate = $this->selectInsuranceFallbackFromRows($candidatePool, $usia);
            if ($rate !== null) {
                return $rate;
            }
        }

        return null;
    }

    private function selectInsuranceRateFromRows($rows, string $product, ?int $tenor, ?int $usia): ?InsuranceRate
    {
        $ageRows = $rows->filter(function (InsuranceRate $row) {
            return trim((string) $row->usia) !== '';
        });

        if ($usia !== null && $ageRows->isNotEmpty()) {
            $ageMatches = $this->selectInsuranceAgeRows($ageRows, $usia);
            if ($ageMatches->isNotEmpty()) {
                $targetTenor = $this->resolveInsuranceTargetTenor($tenor, $product, $ageMatches);
                $rate = $this->selectInsuranceTenorRate($ageMatches, $targetTenor, $tenor);
                if ($rate !== null) {
                    return $rate;
                }
            }
        }

        $ageIndependentRows = $rows->filter(function (InsuranceRate $row) {
            return trim((string) $row->usia) === '';
        });

        return $this->selectInsuranceTenorRate($ageIndependentRows, $tenor, $tenor);
    }

    private function selectInsuranceTenorRate($rows, ?int $targetTenor, ?int $requestedTenor): ?InsuranceRate
    {
        if ($rows->isEmpty()) {
            return null;
        }

        $tenorRows = $rows->filter(function (InsuranceRate $row) {
            return $row->tenor !== null;
        });

        if ($targetTenor !== null && $tenorRows->isNotEmpty()) {
            $tenorMatches = $tenorRows->filter(function (InsuranceRate $row) use ($targetTenor) {
                return (int) $row->tenor >= $targetTenor;
            });

            if ($tenorMatches->isNotEmpty()) {
                return $tenorMatches->sortBy(function (InsuranceRate $row) {
                    return (int) $row->tenor;
                })->first();
            }
        }

        if ($requestedTenor !== null && $requestedTenor > 0) {
            $ageOnlyRows = $rows->filter(function (InsuranceRate $row) {
                return $row->tenor === null;
            });

            return $ageOnlyRows->first();
        }

        if ($tenorRows->isNotEmpty()) {
            return $tenorRows->sortBy(function (InsuranceRate $row) {
                return (int) $row->tenor;
            })->first();
        }

        return $rows->first();
    }

    private function selectInsuranceFallbackFromRows($rows, ?int $usia): ?InsuranceRate
    {
        $ageRows = $rows->filter(function (InsuranceRate $row) {
            return trim((string) $row->usia) !== '';
        });
        $ageIndependentRows = $rows->filter(function (InsuranceRate $row) {
            return trim((string) $row->usia) === '';
        });

        $candidateRows = $ageIndependentRows;
        if ($usia !== null && $ageRows->isNotEmpty()) {
            $ageMatches = $this->selectInsuranceAgeRows($ageRows, $usia);
            if ($ageMatches->isNotEmpty()) {
                $candidateRows = $ageMatches;
            }
        }

        if ($candidateRows->isEmpty()) {
            return null;
        }

        $tenorRows = $candidateRows->filter(function (InsuranceRate $row) {
            return $row->tenor !== null;
        });

        if ($tenorRows->isNotEmpty()) {
            return $tenorRows->sortByDesc(function (InsuranceRate $row) {
                return (int) $row->tenor;
            })->first();
        }

        return $candidateRows->sortByDesc(function (InsuranceRate $row) {
            return (float) $row->premium_per_million;
        })->first();
    }

    private function selectInsuranceAgeRows($rows, int $usia)
    {
        $numericRows = $rows->filter(function (InsuranceRate $row) {
            return is_numeric(trim((string) $row->usia));
        });

        $exactNumericRows = $numericRows->filter(function (InsuranceRate $row) use ($usia) {
            return (int) trim((string) $row->usia) === $usia;
        });
        if ($exactNumericRows->isNotEmpty()) {
            return $exactNumericRows->values();
        }

        $rangeRows = $rows->filter(function (InsuranceRate $row) use ($usia) {
            $range = $this->parseUsiaRange((string) $row->usia);
            return $range !== null && $usia >= $range[0] && $usia <= $range[1];
        });

        if ($rangeRows->isNotEmpty()) {
            $narrowestWidth = $rangeRows->map(function (InsuranceRate $row) {
                $range = $this->parseUsiaRange((string) $row->usia);
                return $range[1] - $range[0];
            })->min();

            return $rangeRows->filter(function (InsuranceRate $row) use ($narrowestWidth) {
                $range = $this->parseUsiaRange((string) $row->usia);
                return ($range[1] - $range[0]) === $narrowestWidth;
            })->values();
        }

        if ($numericRows->isEmpty()) {
            return $numericRows;
        }

        $selectedAge = $numericRows
            ->sortBy(function (InsuranceRate $row) {
                return (int) trim((string) $row->usia);
            })
            ->first(function (InsuranceRate $row) use ($usia) {
                return (int) trim((string) $row->usia) + 1 >= $usia;
            });

        if ($selectedAge === null) {
            return $numericRows->filter(function (InsuranceRate $row) {
                return false;
            });
        }

        $selectedAgeValue = (int) trim((string) $selectedAge->usia);
        return $numericRows->filter(function (InsuranceRate $row) use ($selectedAgeValue) {
            return (int) trim((string) $row->usia) === $selectedAgeValue;
        })->values();
    }

    private function resolveInsuranceTargetTenor(?int $tenor, string $product, $ageRows): ?int
    {
        if ($tenor === null || $tenor <= 0) {
            return null;
        }

        $ageTenorValues = $ageRows->map(function (InsuranceRate $row) {
            return $row->tenor !== null ? (int) $row->tenor : null;
        })->filter(function (?int $tenor): bool {
            return $tenor !== null;
        });

        if (strtolower(trim($product)) === 'regular' && $ageTenorValues->isNotEmpty() && $ageTenorValues->max() <= 15) {
            return (int) ceil($tenor / 12);
        }

        return $tenor;
    }

    private function normalizeInsuranceBank(?string $bank): string
    {
        $value = strtoupper(trim((string) $bank));
        return preg_replace('/^BANK\\s+/', '', $value) ?? $value;
    }

    private function parseUsiaRange(string $rangeText): ?array
    {
        $trimmed = trim($rangeText);
        if (preg_match('/^(\d+)\s*[-–]\s*(\d+)$/', $trimmed, $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        return null;
    }

    public function isDatePastMaxAge(string|Carbon $tanggalLahir, string|Carbon $tanggalAcuan, int $maxAgeYears): bool
    {
        if ($maxAgeYears <= 0) {
            return false;
        }

        $birthDate = $tanggalLahir instanceof Carbon ? $tanggalLahir->copy() : Carbon::parse($tanggalLahir);
        $referenceDate = $tanggalAcuan instanceof Carbon ? $tanggalAcuan->copy() : Carbon::parse($tanggalAcuan);

        if (! $birthDate->isValid() || ! $referenceDate->isValid()) {
            return false;
        }

        $maxAgeBirthday = $birthDate->copy()->addYears($maxAgeYears);

        return $referenceDate->greaterThan($maxAgeBirthday);
    }

    private function buildAgeText(Carbon $tanggalLahir, Carbon $tanggalAcuan): array
        {
        $diff = $tanggalLahir->diff($tanggalAcuan);

        $years = $diff->y;
        $remainingMonths = $diff->m;

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

    private function resolveBlokirAngsuranCount(array $input): int
    {
        $requestedBlokir = $input['blokir_angsuran'] ?? null;

        if ($requestedBlokir !== null && $requestedBlokir !== '') {
            return max(1, min(5, (int) $requestedBlokir));
        }

        return 1;
    }

    private function shouldForceFiveInstallmentBlock(string $bankAsal, string $bankTujuan): bool
    {
        $normalizedBankAsal = strtoupper(trim($bankAsal));
        $normalizedBankTujuan = strtoupper(trim($bankTujuan));

        if ($normalizedBankTujuan !== 'MANTAP') {
            return false;
        }

        return in_array($normalizedBankAsal, [
            'BANK WOORI SAUDARA',
            'BANK BUKOPIN',
            'BANK BTPN',
        ], true);
    }

}
