<?php

namespace App\Http\Controllers;

use App\Models\Lender;
use App\Models\Product;
use App\Models\dataSimulasiPelengkap; // Pastikan penulisan nama model sesuai file asli (D/d)
use App\Models\SimulationField;
use App\Models\User;
use App\Services\PdfTextExtractionService;
use App\Services\SimulationPdfRowMapperService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SimulationController extends Controller
{
    public function __construct(
        private readonly PdfTextExtractionService $pdfTextExtractionService,
        private readonly SimulationPdfRowMapperService $simulationPdfRowMapperService
    ) {
    }

    public function index()
    {
        $products = Product::with(['fields', 'financials.account'])->get();
        $lenders = Lender::query()->orderBy('name')->get(['id', 'name']);
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('products.simulation', compact('products', 'lenders', 'users'));
    }

    public function dataSimulasiIndex()
    {
        return view('products.data_simulasi_upload');
    }

    public function getAccountsByProduct(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::query()->with(['fields', 'financials.account'])->find($productId);

        if ($product) {
            $financials = $product->financials->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'account_id' => $item->account_id,
                    'account_code' => $item->account?->code,
                    'account_name' => $item->account?->name,
                    'calculation_type' => $item->calculation_type,
                    'default_value' => $item->default_value,
                    'transaction_type' => $item->transaction_type,
                    'is_deducted_at_disbursement' => $item->is_deducted_at_disbursement,
                    'is_included_in_simulation' => $item->is_included_in_simulation,
                ];
            })->values();

            return response()->json([
                'fields' => $product->fields,
                'financials' => $financials,
                'accounts' => $financials,
            ]);
        }

        return response()->json([
            'fields' => [],
            'financials' => [],
            'accounts' => [],
        ]);
    }

    public function importOcrRows(Request $request)
    {
        $validated = $request->validate([
            'source_filename' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'loan_id' => ['nullable', 'exists:loans,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.field_label' => ['required', 'string', 'max:255'],
            'rows.*.field_value' => ['nullable', 'string'],
            'rows.*.section' => ['nullable', 'string', 'max:100'],
        ]);

        $batchId = (string) Str::uuid();
        $created = 0;

        DB::transaction(function () use ($validated, $batchId, &$created): void {
            foreach ($validated['rows'] as $index => $row) {
                $key = Str::snake($row['field_label']);

                SimulationField::query()->create([
                    'simulation_batch_id' => $batchId,
                    'source_filename' => $validated['source_filename'] ?? null,
                    'product_id' => $validated['product_id'] ?? null,
                    'loan_id' => $validated['loan_id'] ?? null,
                    'field_key' => $key,
                    'field_label' => $row['field_label'],
                    'field_value' => $row['field_value'] ?? null,
                    'section' => $row['section'] ?? null,
                    'line_order' => $index + 1,
                    'raw_line' => trim(($row['field_label'] ?? '') . ' ' . ($row['field_value'] ?? '')),
                    'extracted_at' => now(),
                ]);

                $created++;
            }
        });

        return response()->json([
            'message' => 'Data simulasi OCR berhasil disimpan.',
            'simulation_batch_id' => $batchId,
            'rows_created' => $created,
        ], 201);
    }

    public function importPdfText(Request $request)
    {
        $validated = $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'product_id' => ['nullable', 'exists:products,id'],
            'loan_id' => ['nullable', 'exists:loans,id'],
        ]);

        $path = $request->file('pdf_file')->store('upload');
        $absolutePath = Storage::path($path);

        $lines = $this->pdfTextExtractionService->extractLines($absolutePath);
        $rows = $this->simulationPdfRowMapperService->mapLines($lines);

        if (empty($rows)) {
            return response()->json([
                'message' => 'Tidak ada baris field yang berhasil dipetakan dari PDF.',
                'stored_path' => $path,
            ], 422);
        }

        $batchId = (string) Str::uuid();
        $created = 0;

        DB::transaction(function () use ($validated, $rows, $batchId, $request, &$created): void {
            foreach ($rows as $row) {
                SimulationField::query()->create([
                    'simulation_batch_id' => $batchId,
                    'source_filename' => $request->file('pdf_file')->getClientOriginalName(),
                    'product_id' => $validated['product_id'] ?? null,
                    'loan_id' => $validated['loan_id'] ?? null,
                    'field_key' => $row['field_key'],
                    'field_label' => $row['field_label'],
                    'field_value' => $row['field_value'],
                    'section' => $row['section'],
                    'line_order' => $row['line_order'],
                    'raw_line' => $row['raw_line'],
                    'extracted_at' => now(),
                ]);
                $created++;
            }
        });

        return response()->json([
            'message' => 'PDF berhasil diproses dengan parser text (tanpa OCR gambar).',
            'simulation_batch_id' => $batchId,
            'rows_created' => $created,
            'stored_path' => $path,
        ], 201);
    }

    public function previewPdfText(Request $request)
    {
        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $path = $request->file('pdf_file')->store('upload');
        $absolutePath = Storage::path($path);

        try {
            $lines = $this->pdfTextExtractionService->extractLines($absolutePath);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membaca teks PDF: ' . $e->getMessage(),
                'stored_path' => $path,
                'rows_count' => 0,
                'rows' => [],
            ], 422);
        }

        $rows = $this->simulationPdfRowMapperService->mapLines($lines);

        $normalizedRows = array_map(function (array $row) {
            return array_merge($row, [
                'normalized_value' => $this->normalizeValue($row['field_label'], $row['field_value']),
            ]);
        }, $rows);

        return response()->json([
            'message' => 'Preview parsing PDF berhasil.',
            'stored_path' => $path,
            'rows_count' => count($normalizedRows),
            'rows' => $normalizedRows,
        ]);
    }

    public function storeDataSimulasi(Request $request)
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.field_key' => ['nullable', 'string', 'max:255'],
            'rows.*.field_label' => ['required', 'string', 'max:255'],
            'rows.*.field_value' => ['nullable'],
            'rows.*.normalized_value' => ['nullable'],
        ]);

        $fillable = [
            'jenis_pensiun', 'nama_debitur', 'tanggal_lahir', 'umur', 'nomor_pensiun',
            'instansi', 'gaji_pensiun', 'sisa_gaji_saat_pengajuan', 'produk', 'mutasi',
            'bank_asal', 'bank_tujuan', 'tenor_max', 'plafond_max', 'tenor', 'nama_marketing',
            'kode_area', 'usia_lunas', 'tgl_permohonan', 'tgl_lunas', 'blokir_angsuran',
            'plafond', 'angsuran', 'biaya_adm_angs', 'total_angsuran', 'provisi',
            'administrasi', 'asuransi', 'extra_premi', 'amount_blokir_angsuran',
            'pelunasan', 'tata_laksana', 'total_biaya', 'sisa_gaji_akhir', 'terima_bersih',
        ];
        $payload = array_fill_keys($fillable, null);
        $occurrences = [];

        foreach ($validated['rows'] as $row) {
            $key = $this->resolveDataSimulasiKey($row, $payload, $occurrences);
            if ($key === null) {
                continue;
            }

            $value = $row['normalized_value'] ?? $row['field_value'] ?? null;

            if ($key === 'umur' && is_string($value)) {
                $parsedAge = $this->parseAgeYearMonth($value);
                if ($parsedAge !== null) {
                    $value = $parsedAge['tahun'];
                }
            }

            $payload[$key] = is_string($value) ? trim($value) : $value;
        }

        $now = now();
        $payload['status'] = 'confirmed';
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;
        $savedId = DB::table('data_simulasi')->insertGetId($payload);

        // Memanggil method pelengkap flagging dengan aman
        $this->savePelengkapFlagging($savedId, $payload);

        return response()->json([
            'message' => 'Data simulasi berhasil disimpan.',
            'id' => $savedId,
            'data' => $payload,
        ], 201);
    }

    public function mapBatchToProposal(Request $request, string $batchId)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $rows = SimulationField::query()
            ->where('simulation_batch_id', $batchId)
            ->orderBy('line_order')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'message' => 'simulation_batch_id tidak ditemukan.',
            ], 404);
        }

        $product = Product::query()->with(['fields', 'financials.account'])->findOrFail($validated['product_id']);

        $rowMap = [];
        foreach ($rows as $row) {
            $normalizedValue = $this->normalizeValue($row->field_label, $row->field_value);
            $labelKey = $this->normalizeKey($row->field_label);
            $fieldKey = $this->normalizeKey($row->field_key);

            if (!array_key_exists($labelKey, $rowMap)) {
                $rowMap[$labelKey] = $normalizedValue;
            }
            if (!array_key_exists($fieldKey, $rowMap)) {
                $rowMap[$fieldKey] = $normalizedValue;
            }
        }

        $debtorData = [];
        $submissionData = [];
        foreach ($product->fields as $field) {
            $value = $this->resolveFromRowMap($rowMap, $this->buildFieldCandidates($field->field_name, $field->field_label));

            $group = Str::lower((string) $field->group);
            if (Str::contains($group, 'debitur')) {
                $debtorData[$field->field_name] = $value;
            }

            if (Str::contains($group, 'pengajuan')) {
                $submissionData[$field->field_name] = $value;
            }
        }

        $financialData = [];
        foreach ($product->financials as $item) {
            $candidate = $this->resolveFromRowMap($rowMap, $this->buildFinancialCandidates($item->item_name));

            if ($candidate === null) {
                continue;
            }

            $financialData[$item->item_name] = $candidate;
        }

        $plafond = $this->resolveFromRowMap($rowMap, ['plafond', 'plafon']);
        $tenor = $this->resolveFromRowMap($rowMap, ['tenor']);

        return response()->json([
            'message' => 'Batch simulasi berhasil dipetakan ke payload pengajuan.',
            'simulation_batch_id' => $batchId,
            'proposal_payload' => [
                'product_id' => (int) $validated['product_id'],
                'amount_plafond' => is_numeric($plafond) ? (float) $plafond : null,
                'debtor_data' => $debtorData,
                'submission_data' => array_merge($submissionData, [
                    'tenor' => is_numeric($tenor) ? (int) $tenor : $tenor,
                ]),
                'financial_data' => $financialData,
            ],
        ]);
    }

    private function normalizeKey(string $value): string
    {
        $normalized = Str::lower(trim($value));
        $normalized = preg_replace('/[^a-z0-9]+/u', '_', $normalized) ?? '';
        return trim($normalized, '_');
    }

    private function resolveFromRowMap(array $rowMap, array $candidates): mixed
    {
        foreach ($candidates as $candidate) {
            $key = $this->normalizeKey((string) $candidate);
            if ($key !== '' && array_key_exists($key, $rowMap)) {
                return $rowMap[$key];
            }
        }

        return null;
    }

    private function resolveDataSimulasiKey(array $row, array $payload, array &$occurrences): ?string
    {
        $rawCandidates = [
            (string) ($row['field_key'] ?? ''),
            (string) ($row['field_label'] ?? ''),
        ];

        foreach ($rawCandidates as $candidate) {
            $direct = $this->normalizeKey($candidate);
            $stripped = $this->normalizeKey(preg_replace('/\([^)]*\)/u', '', $candidate) ?? '');

            foreach ([$direct, $stripped] as $normalized) {
                if ($normalized === '') {
                    continue;
                }

                if (array_key_exists($normalized, $payload)) {
                    return $this->resolveBlockirAngsuranKey($normalized, $occurrences);
                }

                $alias = [
                    'provisi_1_5' => 'provisi',
                    'administrasi_5' => 'administrasi',
                    'adminsitrasi_5' => 'administrasi',
                    'asuransi_10_19' => 'asuransi',
                ];

                if (isset($alias[$normalized])) {
                    return $alias[$normalized];
                }

                if (Str::startsWith($normalized, 'provisi')) {
                    return 'provisi';
                }

                if (Str::startsWith($normalized, 'administrasi') || Str::startsWith($normalized, 'adminsitrasi')) {
                    return 'administrasi';
                }

                if (Str::startsWith($normalized, 'asuransi')) {
                    return 'asuransi';
                }

                if (
                    Str::startsWith($normalized, 'angsuran')
                    && !Str::startsWith($normalized, 'total_angsuran')
                    && !Str::contains($normalized, 'blokir')
                ) {
                    return 'angsuran';
                }
            }
        }

        return null;
    }

    private function resolveBlockirAngsuranKey(string $key, array &$occurrences): string
    {
        if ($key !== 'blokir_angsuran') {
            return $key;
        }

        $occurrences[$key] = ($occurrences[$key] ?? 0) + 1;
        if ($occurrences[$key] > 1) {
            return 'amount_blokir_angsuran';
        }

        return $key;
    }

    private function buildFieldCandidates(string $fieldName, string $fieldLabel): array
    {
        $candidates = [
            $fieldName,
            $fieldLabel,
            str_replace('_', ' ', $fieldName),
        ];

        $fieldKey = $this->normalizeKey($fieldName);
        $aliases = [
            'nopen' => ['nomor_pensiun', 'no_pensiun'],
            'instansi_pensiun' => ['instansi'],
            'gaji_bersih' => ['gaji_pensiun', 'sisa_gaji_saat_pengajuan', 'sisa_gaji_akhir'],
        ];

        foreach ($aliases[$fieldKey] ?? [] as $alias) {
            $candidates[] = $alias;
        }

        return array_values(array_unique($candidates));
    }

    private function buildFinancialCandidates(string $itemName): array
    {
        $candidates = [
            $itemName,
            str_replace('_', ' ', $itemName),
        ];

        $itemKey = $this->normalizeKey($itemName);
        $aliases = [
            'plafon' => ['plafond'],
            'admin_angsuran' => ['biaya_adm_angs', 'biaya_adm_angsuran', 'adm_angsuran'],
            'tatalaksana' => ['tata_laksana'],
            'angsuran' => ['total_angsuran'],
        ];

        foreach ($aliases[$itemKey] ?? [] as $alias) {
            $candidates[] = $alias;
        }

        return array_values(array_unique($candidates));
    }

    private function normalizeValue(string $label, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $isDateLabel = Str::contains(Str::lower($label), ['tgl', 'tanggal']);
        $isAgeLabel = Str::contains(Str::lower($label), ['umur']);

        if ($isAgeLabel) {
            $parsedAge = $this->parseAgeYearMonth($text);
            if ($parsedAge !== null) {
                $ageText = $parsedAge['tahun'] . ' tahun';
                if (($parsedAge['bulan'] ?? 0) > 0) {
                    $ageText .= ' ' . $parsedAge['bulan'] . ' bulan';
                }

                return $ageText;
            }
        }

        if ($isDateLabel) {
            $parsedDate = $this->parseIndonesianDate($text);
            if ($parsedDate) {
                return $parsedDate;
            }

            return $text;
        }

        if (preg_match('/rp/i', $text) || preg_match('/\d/u', $text)) {
            $numeric = $this->parseLocalizedNumber($text);
            if ($numeric !== null) {
                return $numeric;
            }
        }

        if (preg_match('/^\d+$/', $text)) {
            return (int) $text;
        }

        return $text;
    }

    private function parseLocalizedNumber(string $value): ?float
    {
        $cleaned = preg_replace('/[^0-9,.-]/u', '', $value) ?? '';
        if ($cleaned === '' || $cleaned === '-' || $cleaned === ',' || $cleaned === '.') {
            return null;
        }

        $lastComma = strrpos($cleaned, ',');
        $lastDot = strrpos($cleaned, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $normalized = str_replace('.', '', $cleaned);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $cleaned);
            }

            return is_numeric($normalized) ? (float) $normalized : null;
        }

        if ($lastComma !== false) {
            $commaCount = substr_count($cleaned, ',');
            if ($commaCount > 1 || preg_match('/,\d{3}(?:,\d{3})*$/', $cleaned)) {
                $normalized = str_replace(',', '', $cleaned);
            } else {
                $normalized = str_replace(',', '.', $cleaned);
            }

            return is_numeric($normalized) ? (float) $normalized : null;
        }

        if ($lastDot !== false) {
            $dotCount = substr_count($cleaned, '.');
            if ($dotCount > 1 || preg_match('/\.\d{3}(?:\.\d{3})*$/', $cleaned)) {
                $normalized = str_replace('.', '', $cleaned);
            } else {
                $normalized = $cleaned;
            }

            return is_numeric($normalized) ? (float) $normalized : null;
        }

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    private function parseIndonesianDate(string $value): ?string
    {
        $trimmed = trim($value);

        if (preg_match('/^\d{5}(?:\.0+)?$/', $trimmed) === 1) {
            try {
                $serial = (int) floor((float) $trimmed);
                return Carbon::create(1899, 12, 30)->addDays($serial)->toDateString();
            } catch (\Throwable) {
            }
        }

        $months = [
            'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
            'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
            'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
        ];

        if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/u', $trimmed, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $monthName = Str::lower($m[2]);
            $year = $m[3];

            if (isset($months[$monthName])) {
                return $year . '-' . $months[$monthName] . '-' . $day;
            }
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $trimmed, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $trimmed, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        try {
            return Carbon::parse($trimmed)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseAgeYearMonth(string $value): ?array
    {
        $text = Str::lower(trim($value));
        if ($text === '') {
            return null;
        }

        $year = null;
        $month = null;

        if (preg_match('/(\d{1,3})\s*(?:th|thn|tahun)\b/u', $text, $m)) {
            $year = (int) $m[1];
        }

        if (preg_match('/(\d{1,2})\s*(?:bln|bulan)\b/u', $text, $m)) {
            $month = (int) $m[1];
        }

        if ($year === null && $month === null) {
            return null;
        }

        return [
            'tahun' => $year ?? 0,
            'bulan' => $month ?? 0,
        ];
    }

    // --- BAGIAN REFACTOR & OPTIMASI GENERATOR NOMOR ---

    private function savePelengkapFlagging(int $dataSimulasiId, array $payload): void
    {
        $flagging = $this->resolveFlaggingByInstansi((string) ($payload['instansi'] ?? ''));

        $upsertPayload = [
            'no_pk'       => self::generateNomorPK(),
            'no_sppk'     => self::generateNomorSPPK(),
            'no_si'       => $this->generateNomorSI(),
            'suku_bunga'  => 10,
            'prosentaseprovisi'=>0.5,
            'prosentaseadmini'=>0.5, 
            'updated_at'  => now(),
        ];

        if ($flagging !== null) {
            if (Schema::hasColumn('data_simulasi_pelengkap', 'biaya_flagging')) {
                $upsertPayload['biaya_flagging'] = $flagging;
            }

            if (Schema::hasColumn('data_simulasi_pelengkap', 'flagging')) {
                $upsertPayload['flagging'] = $flagging;
            }
        }

        DB::table('data_simulasi_pelengkap')->updateOrInsert(
            ['data_simulasi_id' => $dataSimulasiId],
            array_merge(['created_at' => now()], $upsertPayload)
        );
    }

    public function generateNomorSI(): string
    {
        $now = Carbon::now();
        $tahun = $now->year;
        $bulan = $now->month;
        $romawi = self::konversiKeRomawi($bulan);

        // OPTIMASI: Ganti whereYear dengan range tanggal agar index-friendly (Cepat & Anti-Timeout)
        $terakhir = dataSimulasiPelengkap::query()
            ->whereBetween('created_at', ["{$tahun}-01-01 00:00:00", "{$tahun}-12-31 23:59:59"])
            ->whereNotNull('no_si')
            ->where('no_si', '!=', '')
            ->orderBy('id', 'desc')
            ->first(['no_si']);            

        $urutBaru = 1;

        if ($terakhir) {
            $nomorLama = explode('/', $terakhir->no_si)[0]; 
            // OPTIMASI: Menggunakan Regex untuk mencari angka agar kebal dari error "Undefined array key 1"
            if (preg_match('/(\d+)/', $nomorLama, $matches)) {
                $urutBaru = (int) $matches[1] + 1;
            }
        }

        $noUrutStr = str_pad($urutBaru, 4, '0', STR_PAD_LEFT);

        return "SI-TO.{$noUrutStr}/NBP_CH.KB/{$romawi}/{$tahun}";
    }

    public static function generateNomorPK(): string
    {
        $now = Carbon::now();
        $tahun = $now->year;
        $bulan = $now->month;
        $romawi = self::konversiKeRomawi($bulan);

        $terakhir = dataSimulasiPelengkap::query()
            ->whereBetween('created_at', ["{$tahun}-01-01 00:00:00", "{$tahun}-12-31 23:59:59"])
            ->whereNotNull('no_pk')
            ->where('no_pk', '!=', '')
            ->orderBy('id', 'desc')
            ->first(['no_pk']);

        $urutBaru = 1;

        if ($terakhir) {
            $nomorLama = explode('/', $terakhir->no_pk)[0]; 
            if (preg_match('/(\d+)/', $nomorLama, $matches)) {
                $urutBaru = (int) $matches[1] + 1;
            }
        }

        $noUrutStr = str_pad($urutBaru, 4, '0', STR_PAD_LEFT);

        return "PK.{$noUrutStr}/NBP_CH.KB/{$romawi}/{$tahun}";
    }

    public static function generateNomorSPPK(): string
    {
        $now = Carbon::now();
        $tahun = $now->year;
        $bulan = $now->month;
        $romawi = self::konversiKeRomawi($bulan);

        $terakhir = dataSimulasiPelengkap::query()
            ->whereBetween('created_at', ["{$tahun}-01-01 00:00:00", "{$tahun}-12-31 23:59:59"])
            ->whereNotNull('no_sppk')
            ->where('no_sppk', '!=', '')
            ->orderBy('id', 'desc')
            ->first(['no_sppk']);

        $urutBaru = 1;

        if ($terakhir) {
            $nomorLama = explode('/', $terakhir->no_sppk)[0]; 
            if (preg_match('/(\d+)/', $nomorLama, $matches)) {
                $urutBaru = (int) $matches[1] + 1;
            }
        }

        $noUrutStr = str_pad($urutBaru, 4, '0', STR_PAD_LEFT);

        return "{$noUrutStr}/SPPK/KNBP-KB/{$romawi}/{$tahun}";
    }

    private static function konversiKeRomawi(int $bulan): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        return $map[$bulan] ?? 'I';
    }

    private function resolveFlaggingByInstansi(string $instansi): ?float
    {
        $normalized = Str::upper(trim($instansi));

        return match ($normalized) {
            'TASPEN' => 816000.0,
            'ASABRI' => 350000.0,
            default => null,
        };
    }
}