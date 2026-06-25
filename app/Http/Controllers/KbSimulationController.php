<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use App\Models\ProductStruct;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\KbSimulationExcelService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KbSimulationController extends Controller
{
    public function __construct(private readonly KbSimulationExcelService $kbSimulationExcelService)
    {
    }

    public function index(Request $request)
    {
        $options = $this->kbSimulationExcelService->getSelectOptions();
        $productStructs = ProductStruct::query()
            ->orderBy('sort_order')
            ->get([
                'produk',
                'tenor_max',
                'usia_max',
                'usia_masuk_min',
                'usia_masuk_max',
                'rate_percent',
                'dbr_percent',
                'admin_angsuran_percent',
                'provisi_percent',
                'admin_percent',
                'blokir_angsuran',
            ])
            ->mapWithKeys(function (ProductStruct $item) {
                return [
                    $item->produk => [
                        'tenor_max' => (int) ($item->tenor_max ?? 0),
                        'usia_max' => (int) ($item->usia_max ?? 0),
                        'usia_masuk_min' => (int) ($item->usia_masuk_min ?? 0),
                        'usia_masuk_max' => (int) ($item->usia_masuk_max ?? 0),
                        'rate_percent' => (float) ($item->rate_percent ?? 0),
                        'dbr_percent' => (float) ($item->dbr_percent ?? 0),
                        'admin_angsuran_percent' => (float) ($item->admin_angsuran_percent ?? 0),
                        'provisi_percent' => (float) ($item->provisi_percent ?? 0),
                        'admin_percent' => (float) ($item->admin_percent ?? 0),
                        'blokir_angsuran' => (int) ($item->blokir_angsuran ?? 0),
                    ],
                ];
            })
            ->all();

        /** @var User|null $user */
        $user = Auth::user();

        $userRole = $user?->roleSlug() ?? User::ROLE_MARKETING;
        $canEditPricing = $user?->canEditKbPricing() ?? false;

        $initialData = null;
        $editId = $request->query('edit_data_simulasi');
        if ($editId !== null && $editId !== '') {
            $record = DataSimulasi::query()->find($editId);
            if ($record) {
                $initialData = [
                    'id' => $record->id,
                    'produk' => $record->produk,
                    'jenis_pensiun' => $record->jenis_pensiun,
                    'mutasi' => $record->mutasi,
                    'bank_tujuan' => $record->bank_tujuan,
                    'bank_asal' => $record->bank_asal,
                    'keterangan' => $record->keterangan,
                    'nama_debitur' => $record->nama_debitur,
                    'tanggal_simulasi' => optional($record->tgl_permohonan)?->toDateString(),
                    'tanggal_lahir' => optional($record->tanggal_lahir)?->toDateString(),
                    'nomor_pensiun' => $record->nomor_pensiun,
                    'instansi' => $record->instansi,
                    'gaji_pensiun' => $record->gaji_pensiun,
                    'angsuran_lainnya' => null,
                    'blokir_angsuran' => $record->blokir_angsuran,
                    'umur_text' => $record->umur !== null ? ((int) $record->umur . ' thn') : null,
                    'tenor_max' => $record->tenor_max,
                    'tenor' => $record->tenor,
                    'plafond' => $record->plafond,
                    'pelunasan' => $record->pelunasan,
                    'nama_marketing' => $record->nama_marketing,
                    'kode_area' => $record->kode_area,
                    'rate_percent_override' => $record->rate_percent_override,
                    'admin_angsuran_percent_override' => $record->admin_angsuran_percent_override,
                ];
            }
        }

        return view('products.simulasi_kb_form', compact('options', 'productStructs', 'initialData', 'userRole', 'canEditPricing'));
    }

    public function calculate(Request $request): JsonResponse
    {
        $this->ensurePricingOverridesAuthorized($request);

        $input = $request->validate($this->calculateRules());

        $input = array_merge([
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_asal' => 'BANK BUKOPIN',
            'bank_tujuan' => 'KB',
            'keterangan' => '',
            'nama_debitur' => '-',
            'tanggal_simulasi' => now()->toDateString(),
            'tanggal_lahir' => null,
            'nomor_pensiun' => '-',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 0,
            'angsuran_lainnya' => 0,
            'tenor' => null,
            'plafond' => null,
            'nama_marketing' => '-',
            'kode_area' => '-',
        ], $input);

        $cacheKey = 'kb_simulasi_calc_' . sha1(json_encode($input));

        try {
            $result = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($input) {
                return $this->kbSimulationExcelService->calculate($input);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $limitChecks = $this->buildLimitChecks($input, $result);

        return response()->json([
            'message' => 'Perhitungan simulasi berhasil.',
            'data' => $result,
            'display' => $this->buildDisplayResult($result),
            'limits' => $limitChecks,
        ]);
    }
public function store(Request $request): JsonResponse
    {
        $this->ensurePricingOverridesAuthorized($request);
        Log::info('Storing KB simulation data...');
        $isClientSide = $request->boolean('client_side_calculation');
        Log::info('Client side calculation: ' . ($isClientSide ? 'true' : 'false'));
        
        $validator = Validator::make($request->all(), $this->storeRules());
        if ($validator->fails()) {
            Log::error('VALIDASI GAGAL DI FIELD: ' . json_encode($validator->errors()->toArray()));
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $input = $validator->validated();        
        Log::info('Input data: ' . json_encode($input));

        if ($isClientSide) {
            Log::info('Validating client result data...');
            $clientResult = $request->validate($this->clientResultRules());
            $result = array_merge($input, $clientResult);
        } else {
            Log::info('Calculating KB simulation data...');
            try {
                $result = $this->kbSimulationExcelService->calculate($input);
            } catch (\Throwable $e) {
                Log::error('Error during KB simulation calculation: ' . $e->getMessage());
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        // ==================== LANGSUNG MERGE DAN SAVE ====================
        // Gabungkan data input (nama, no pensiun, dll) dan hasil perhitungan keuangan
        $persistPayload = array_merge($input, $result);

        // Bersihkan field pembantu yang tidak ada di kolom database Anda
        unset(
            $persistPayload['umur_text'], 
            $persistPayload['usia_lunas_text'], 
            $persistPayload['angsuran_lainnya'],
            $persistPayload['client_side_calculation']
        );

        $persistPayload['status'] = 'trial';

        // Langsung simpan ke database tanpa limitcheck
        $saved = DataSimulasi::query()->create($persistPayload);
        Log::info('Data simulasi KB berhasil disimpan dengan ID: ' . $saved->id);
        // =================================================================

        return response()->json([
            'message' => 'Data simulasi KB berhasil disimpan.',
            'id' => $saved->id,
            'data' => $saved,
            'display' => $this->buildDisplayResult($result),
        ], 201);
    }
    // public function store(Request $request): JsonResponse
    // {
    //     Log::Info('Storing KB simulation data...');
    //     Log::info('Request data: ' . json_encode($request->all()));
    //    // $this->ensurePricingOverridesAuthorized($request);
    //     Log::info('Storing KB simulation data...');
    //     $isClientSide = $request->boolean('client_side_calculation');
    //     Log::info('Client side calculation: ' . ($isClientSide ? 'true' : 'false'));
    //     //$input = $request->validate($this->storeRules());
    //     $validator = Validator::make($request->all(), $this->storeRules());
    //     if ($validator->fails()) {
    //         Log::error('VALIDASI GAGAL DI FIELD: ' . json_encode($validator->errors()->toArray()));
    //         return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    //     }
    //     $input = $validator->validated();        
    //     Log::info('Input data: ' . json_encode($input));
    //     if ($isClientSide) {
    //         Log::info('Validating client result data...');
    //         $clientResult = $request->validate($this->clientResultRules());
    //         $result = array_merge($input, $clientResult);
    //     } else {
    //         Log::info('Calculating KB simulation data...');
    //         try {
    //             $result = $this->kbSimulationExcelService->calculate($input);
    //         } catch (\Throwable $e) {
    //             Log::error('Error during KB simulation calculation: ' . $e->getMessage());
    //             return response()->json([
    //                 'message' => $e->getMessage(),
    //             ], 422);
    //         }
    //     }

    //     $limitChecks = $this->buildLimitChecks($input, $result);
    //     Log::info('Limit checks: ' . json_encode($limitChecks));
    //     if (!$limitChecks['is_valid']) {
    //         Log::warning('Invalid limit checks detected.');
    //         return response()->json([
    //             'message' => 'Pengajuan ditolak karena ada batasan kelayakan yang tidak terpenuhi.',
    //             'limits' => $limitChecks,
    //             'display' => $this->buildDisplayResult($result),
    //             'data' => $result,
    //         ], 422);
    //     }

    //     $persistPayload = $result;

    //     unset($persistPayload['umur_text'], $persistPayload['usia_lunas_text'], $persistPayload['angsuran_lainnya']);

    //     $saved = DataSimulasi::query()->create($persistPayload);

    //     return response()->json([
    //         'message' => 'Data simulasi KB berhasil disimpan.',
    //         'id' => $saved->id,
    //         'data' => $saved,
    //         'display' => $this->buildDisplayResult($result),
    //         'limits' => $limitChecks,
    //     ], 201);
    // }

    public function downloadPdf(Request $request)
{
    // Gunakan validator manual agar jika ada field kurang tidak langsung melempar error HTML Redirect
    //$validator = \Illuminate\Support\Facades\Validator::make($request->all(), $this->calculateRules());
    
    // Ambil data yang lolos validasi, atau ambil langsung dari request jika ada yang miss
    //$input = $request->all();

    // Pastikan data personal dasar terisi aman sebagai fallback
    // $input = array_merge([
    //     'produk' => 'Platinum',
    //     'jenis_pensiun' => 'Sendiri',
    //     'bank_tujuan' => 'KB',
    //     'bank_asal' => '',
    //     'nama_debitur' => '-',
    //     'tanggal_simulasi' => now()->toDateString(),
    //     'tanggal_lahir' => null,
    //     'nomor_pensiun' => '-',
    //     'instansi' => 'TASPEN',
    //     'gaji_pensiun' => 0,
    //     'angsuran_lainnya' => 0,
    //     'tenor' => 1,
    //     'plafond' => 0,
    //     'nama_marketing' => '-',
    //     'kode_area' => '-',
    // ], $input);
    $validated = $request->validate([
        'id' => ['required', 'integer', 'exists:data_simulasi,id'],
    ]);

    $sim = DataSimulasi::query()->find($validated['id']);
    if ($sim === null) {
        return response()->json([
            'message' => 'Data simulasi tidak ditemukan.',
        ], 404);
    }

    // if ($isClientSide) {
    //     $result = $input; // Jika client-side, gunakan langsung gabungan data dari frontend
    // } else {
    //     try {
    //         // Hitung ulang via excel service untuk memastikan kecocokan angka
    //         $result = $this->kbSimulationExcelService->calculate($input);
    //     } catch (\Throwable $e) {
    //         // Catat ke log file jika excel service mendadak error
    //         \Illuminate\Support\Facades\Log::error('PDF Calculation Fail: ' . $e->getMessage());
    //         return response()->json(['message' => 'Gagal menghitung ulang simulasi untuk PDF: ' . $e->getMessage()], 422);
    //     }
    // }

    // // Prepare objects expected by the legacy blade `simulasifordownload`
    // $simArray = array_merge($input, $result); // Merge agar semua field bersatu aman
    // $simArray['notas'] = $request->input('nomor_pensiun') ?? $result['nomor_pensiun'] ?? '-';
    // $simArray['created_at'] = \Illuminate\Support\Carbon::parse($input['tanggal_simulasi'] ?? now());
    // $simArray['usia'] = $result['umur'] ?? null;
    // $simArray['instansi'] = $input['instansi'] ?? ($result['instansi'] ?? null);
    // $simArray['product_kode'] = $input['product_kode'] ?? $input['produk'] ?? null;
    //$sim = (object) $simArray;

   

    // load logo
    $logoPath = public_path('img/Logo_nata.jpeg');
    $logo = '';
    if (file_exists($logoPath)) {
        $logo = base64_encode(file_get_contents($logoPath));
    }
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('products.simulasifordownload', [
        'sim' => $sim,
        'logo' => $logo,
        'generatedAt' => now(),
    ])->setPaper('a4', 'portrait');

    $filename = 'simulasi-kb-' . now()->format('Ymd-His') . '.pdf';

    $binary = $pdf->output();
    return response($binary, 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
        ->header('Content-Length', strlen($binary));
}

    private function buildLimitChecks(array $input, array $result): array
    {
        $productKey = trim((string) ($input['produk'] ?? '') . '-' . (string) ($input['jenis_pensiun'] ?? ''));
        $struct = ProductStruct::query()
            ->where('produk', $productKey)
            ->first();

        $usia = isset($result['umur']) && $result['umur'] !== null ? (int) $result['umur'] : null;
        $usiaMasukMin = $struct?->usia_masuk_min !== null ? (int) $struct->usia_masuk_min : null;
        $usiaLunasMax = $struct?->usia_max !== null ? (int) $struct->usia_max : null;

        $tenorInputRaw = $input['tenor'] ?? null;
        $plafondInputRaw = $input['plafond'] ?? null;

        $tenorInput = ($tenorInputRaw === null || $tenorInputRaw === '') ? null : (int) $tenorInputRaw;
        $plafondInput = ($plafondInputRaw === null || $plafondInputRaw === '') ? null : (float) $plafondInputRaw;
        $tenorMax = (int) ($result['tenor_max'] ?? 0);
        $plafondMax = (float) ($result['plafond_max'] ?? 0);

        $tenorValid = $tenorInput === null ? true : ($tenorInput <= $tenorMax);
        $plafondValid = $plafondInput === null ? true : ($plafondInput <= $plafondMax);

        $usiaMinValid = $usiaMasukMin === null || $usia === null ? true : ($usia >= $usiaMasukMin);
        $usiaMaxValid = $usiaLunasMax === null || $usia === null ? true : ($usia <= $usiaLunasMax);

        $sisaGajiSaatPengajuan = (float) ($result['sisa_gaji_saat_pengajuan'] ?? 0);
        $totalAngsuran = (float) ($result['total_angsuran'] ?? 0);
        $angsuranMax = $sisaGajiSaatPengajuan - 125000;
        $totalAngsuranValid = $totalAngsuran <= $angsuranMax;

        $sisaGajiAkhir = (float) ($result['sisa_gaji_akhir'] ?? 0);
        $sisaGajiAkhirMin = 110000.0;
        $sisaGajiAkhirValid = $sisaGajiAkhir >= $sisaGajiAkhirMin;

        $terimaBersih = (float) ($result['terima_bersih'] ?? 0);
        $terimaBersihValid = $terimaBersih > 0;

        return [
            'usia' => $usia,
            'usia_masuk_min' => $usiaMasukMin,
            'usia_lunas_max' => $usiaLunasMax,
            'usia_min_valid' => $usiaMinValid,
            'usia_max_valid' => $usiaMaxValid,
            'tenor_input' => $tenorInput,
            'tenor_max' => $tenorMax,
            'tenor_valid' => $tenorValid,
            'plafond_input' => $plafondInput,
            'plafond_max' => $plafondMax,
            'plafond_valid' => $plafondValid,
            'total_angsuran' => $totalAngsuran,
            'angsuran_max' => $angsuranMax,
            'total_angsuran_valid' => $totalAngsuranValid,
            'sisa_gaji_akhir' => $sisaGajiAkhir,
            'sisa_gaji_akhir_min' => $sisaGajiAkhirMin,
            'sisa_gaji_akhir_valid' => $sisaGajiAkhirValid,
            'terima_bersih' => $terimaBersih,
            'terima_bersih_valid' => $terimaBersihValid,
            'is_valid' => $usiaMinValid
                && $usiaMaxValid
                && $tenorValid
                && $plafondValid
                && $totalAngsuranValid
                && $sisaGajiAkhirValid
                && $terimaBersihValid,
        ];
    }

    private function buildDisplayResult(array $result): array
    {
        return [
            ['label' => 'E10 - Produk', 'value' => (string) ($result['produk'] ?? '')],
            ['label' => 'E11 - Jenis Pensiun', 'value' => (string) ($result['jenis_pensiun'] ?? '')],
            ['label' => 'E13 - Bank Asal', 'value' => (string) ($result['bank_asal'] ?? '')],
            ['label' => 'E14 - Bank Tujuan/Kantor Bayar', 'value' => (string) ($result['bank_tujuan'] ?? '')],
            ['label' => 'E18 - Nama Debitur', 'value' => (string) ($result['nama_debitur'] ?? '')],
            ['label' => 'E21 - Nomor Pensiun', 'value' => (string) ($result['nomor_pensiun'] ?? '')],
            ['label' => 'E22 - Instansi Pensiun', 'value' => (string) ($result['instansi'] ?? '')],
            ['label' => 'E23 - Gaji Pensiun', 'value' => $this->formatCurrency((float) ($result['gaji_pensiun'] ?? 0))],
            ['label' => 'E20 - Umur', 'value' => (string) ($result['umur_text'] ?? '')],
            ['label' => 'E25 - Sisa Gaji Saat Pengajuan', 'value' => $this->formatCurrency((float) ($result['sisa_gaji_saat_pengajuan'] ?? 0))],
            ['label' => 'E26 - Tenor Max', 'value' => $this->formatInteger((int) ($result['tenor_max'] ?? 0)) . ' bulan'],
            ['label' => 'E27 - Plafond Max', 'value' => $this->formatCurrency((float) ($result['plafond_max'] ?? 0))],
            ['label' => 'E28 - Tenor Input', 'value' => $this->formatInteger((int) ($result['tenor'] ?? 0)) . ' bulan'],
            ['label' => 'E29 - Plafond Input', 'value' => $this->formatCurrency((float) ($result['plafond'] ?? 0))],
            ['label' => 'E31 - Angsuran', 'value' => $this->formatCurrency((float) ($result['angsuran'] ?? 0))],
            ['label' => 'E32 - Biaya Adm Angsuran', 'value' => $this->formatCurrency((float) ($result['biaya_adm_angs'] ?? 0))],
            ['label' => 'E33 - Total Angsuran', 'value' => $this->formatCurrency((float) ($result['total_angsuran'] ?? 0))],
            ['label' => 'E35 - Provisi', 'value' => $this->formatCurrency((float) ($result['provisi'] ?? 0))],
            ['label' => 'E36 - Administrasi', 'value' => $this->formatCurrency((float) ($result['administrasi'] ?? 0))],
            ['label' => 'E37 - Asuransi', 'value' => $this->formatCurrency((float) ($result['asuransi'] ?? 0))],
            ['label' => 'E39 - Pelunasan', 'value' => $this->formatCurrency((float) ($result['pelunasan'] ?? 0))],
            ['label' => 'E39 - Blokir Amount', 'value' => $this->formatCurrency((float) ($result['amount_blokir_angsuran'] ?? 0))],
            ['label' => 'E43 - Nama Marketing', 'value' => (string) ($result['nama_marketing'] ?? '')],
            ['label' => 'E44 - Area', 'value' => (string) ($result['kode_area'] ?? '')],
            ['label' => 'E46 - Usia Lunas', 'value' => (string) ($result['usia_lunas_text'] ?? '')],
            ['label' => 'E51 - Total Biaya', 'value' => $this->formatCurrency((float) ($result['total_biaya'] ?? 0))],
            ['label' => 'E52 - Sisa Gaji Akhir', 'value' => $this->formatCurrency((float) ($result['sisa_gaji_akhir'] ?? 0))],
            ['label' => 'E54 - Terima Bersih', 'value' => $this->formatCurrency((float) ($result['terima_bersih'] ?? 0))],
        ];
    }

    private function formatCurrency(float $value): string
    {
        return 'Rp ' . number_format($value, 2, ',', '.');
    }

    private function formatInteger(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function calculateRules(): array
    {
        return [
            'produk' => ['nullable', 'string', 'max:100'],
            'jenis_pensiun' => ['required', 'string', 'max:100'],
            'mutasi' => ['nullable', 'string', 'in:Mutasi,Non Mutasi'],
            'bank_asal' => ['nullable', 'string', 'max:255'],
            'bank_tujuan' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'nama_debitur' => ['nullable', 'string', 'max:255'],
            'tanggal_simulasi' => ['required', 'date'],
            'tanggal_lahir' => ['required', 'date'],
            'nomor_pensiun' => ['nullable', 'string', 'max:100'],
            'instansi' => ['nullable', 'string', 'max:100'],
            'gaji_pensiun' => ['nullable', 'numeric', 'min:0'],
            'angsuran_lainnya' => ['nullable', 'numeric', 'min:0'],
            'blokir_angsuran' => ['nullable', 'integer', 'in:1,2,3'],
            'pelunasan' => ['nullable', 'numeric', 'min:0'],
            'rate_percent_override' => ['nullable', 'numeric', 'min:0'],
            'admin_angsuran_percent_override' => ['nullable', 'numeric', 'min:0'],
            'tenor' => ['nullable', 'integer', 'min:1'],
            'plafond' => ['nullable', 'numeric', 'min:0'],
            'nama_marketing' => ['nullable', 'string', 'max:255'],
            'kode_area' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function storeRules(): array
    {
        return [
            'produk' => ['required', 'string', 'max:100'],
            'jenis_pensiun' => ['required', 'string', 'max:100'],
           'mutasi' => ['nullable', 'string', 'in:Mutasi,Non Mutasi,MUTASI,NON MUTASI,mutasi,non mutasi'],
            'bank_asal' => ['nullable', 'string', 'max:255'],
            'bank_tujuan' => ['nullable', 'string', 'max:255'],
            'nama_debitur' => ['nullable', 'string', 'max:255'],
            'tanggal_simulasi' => ['required', 'date'],
            'tanggal_lahir' => ['required', 'date'],
            'nomor_pensiun' => ['nullable', 'string', 'max:100'],
            'instansi' => ['nullable', 'string', 'max:100'],
            'gaji_pensiun' => ['nullable', 'numeric', 'min:0'],
            'angsuran_lainnya' => ['nullable', 'numeric', 'min:0'],
            'blokir_angsuran' => ['nullable', 'integer', 'in:1,2,3'],
            'pelunasan' => ['nullable', 'numeric', 'min:0'],
            'rate_percent_override' => ['nullable', 'numeric', 'min:0'],
            'admin_angsuran_percent_override' => ['nullable', 'numeric', 'min:0'],
            'tenor' => ['required', 'integer', 'min:1'],
            'plafond' => ['required', 'numeric', 'min:0'],
            'nama_marketing' => ['nullable', 'string', 'max:255'],
            'kode_area' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function clientResultRules(): array
    {
        return [
            'mutasi' => ['nullable', 'string', 'in:Mutasi,Non Mutasi,MUTASI,NON MUTASI'],
            'umur_text' => ['nullable', 'string', 'max:100'],
            'tenor_max' => ['nullable', 'integer', 'min:0'],
            'plafond_max' => ['nullable', 'numeric', 'min:0'],
            'angsuran' => ['nullable', 'numeric'],
            'biaya_adm_angs' => ['nullable', 'numeric'],
            'total_angsuran' => ['nullable', 'numeric'],
            'provisi' => ['nullable', 'numeric'],
            'administrasi' => ['nullable', 'numeric'],
            'asuransi' => ['nullable', 'numeric'],
            'extra_premi' => ['nullable', 'numeric'],
            'amount_blokir_angsuran' => ['nullable', 'numeric'],
            'blokir_angsuran' => ['nullable', 'numeric'],
            'pelunasan' => ['nullable', 'numeric'],
            'tata_laksana' => ['nullable', 'numeric'],
            'total_biaya' => ['nullable', 'numeric'],
            'sisa_gaji_saat_pengajuan' => ['nullable', 'numeric'],
            'sisa_gaji_akhir' => ['nullable', 'numeric'],
            'terima_bersih' => ['nullable', 'numeric'],
            'usia_lunas_text' => ['nullable', 'string', 'max:100'],
            'tgl_permohonan' => ['nullable', 'date'],
            'tgl_lunas' => ['nullable', 'date'],
        ];
    }

    private function ensurePricingOverridesAuthorized(Request $request): void
    {
        if (! $this->hasPricingOverrideInput($request)) {
            return;
        }

        /** @var User|null $user */
        $user = $request->user();

        abort_unless($user?->canEditKbPricing(), 403, 'Anda tidak memiliki akses untuk mengubah override pricing.');
    }

    private function hasPricingOverrideInput(Request $request): bool
    {
        foreach (['rate_percent_override', 'admin_angsuran_percent_override'] as $field) {
            $value = $request->input($field);

            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    private function filterPdfDisplayRows(array $rows): array
    {
        return array_values(array_filter($rows, static function (array $row): bool {
            $label = strtolower((string) ($row['label'] ?? ''));

            if (str_contains($label, 'rate')) {
                return false;
            }

            if (str_contains($label, 'adm angsuran') && str_contains($label, '%')) {
                return false;
            }

            return true;
        }));
    }
}
