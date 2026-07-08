<?php

namespace App\Http\Controllers;

use App\Models\Banpot;
use App\Services\BanpotExcelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BanpotController extends Controller
{
    private const MONTHS = [
        ['value' => 1, 'label' => 'Januari'],
        ['value' => 2, 'label' => 'Februari'],
        ['value' => 3, 'label' => 'Maret'],
        ['value' => 4, 'label' => 'April'],
        ['value' => 5, 'label' => 'Mei'],
        ['value' => 6, 'label' => 'Juni'],
        ['value' => 7, 'label' => 'Juli'],
        ['value' => 8, 'label' => 'Agustus'],
        ['value' => 9, 'label' => 'September'],
        ['value' => 10, 'label' => 'Oktober'],
        ['value' => 11, 'label' => 'November'],
        ['value' => 12, 'label' => 'Desember'],
    ];

    private const BANKS = ['KB', 'MANTAP'];

    private const BANPOT_COLUMN_MAP = [
        'NO' => 'no',
        'NO PK' => 'no_pk',
        'NOPEN' => 'nopen',
        'NOREK' => 'norek',
        'TOTAL' => 'total',
        'CABANG' => 'cabang',
        'STATUS' => 'status',
        'PLAFOND' => 'plafond',
        'SELISIH' => 'selisih',
        'GIRO MITRA' => 'giro_mitra',
        'KETERANGAN' => 'keterangan',
        'CUSTOMER ID' => 'customer_id',
        'NAMA DEBITUR' => 'nama_debitur',
        'JENIS TAGIHAN' => 'jenis_tagihan',
        'PENGELOLA PENSIUN' => 'pengelola_pensiun',
        'PENDEBETAN ANGSURAN' => 'pendebetan_angsuran',
        'NAMA MITRA CHANNELING' => 'nama_mitra_channeling',
    ];

    public function __construct(private readonly BanpotExcelService $banpotExcelService)
    {
    }

    public function index()
    {
        return redirect()->route('banpot.index');
    }

    public function create()
    {
        $currentYear = (int) now()->year;

        $years = collect(range($currentYear - 5, $currentYear + 5))
            ->sortDesc()
            ->values()
            ->all();

        $months = self::MONTHS;
        $banks = self::BANKS;

        return view('banpot.import', compact('years', 'months', 'banks'));
    }

    public function list(Request $request)
    {
        $currentYear = (int) now()->year;

        $years = collect(range($currentYear - 5, $currentYear + 5))
            ->sortDesc()
            ->values()
            ->all();

        $months = self::MONTHS;
        $banks = self::BANKS;

        $filters = $request->validate([
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'between:1900,2100'],
            'bank' => ['nullable', 'in:KB,MANTAP'],
            'nama_debitur' => ['nullable', 'string', 'max:255'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'nopen' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Banpot::query()->latest('id');

        $hasAnyFilter = collect($filters)
            ->filter(static fn ($value) => $value !== null && $value !== '')
            ->isNotEmpty();

        if (!$hasAnyFilter) {
            $latestBatchId = Banpot::query()
                ->whereNotNull('import_batch_id')
                ->latest('id')
                ->value('import_batch_id');

            if ($latestBatchId !== null) {
                $query->where('import_batch_id', $latestBatchId);
            }
        }

        foreach ($filters as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if ($field === 'instansi') {
                $query->where('pengelola_pensiun', 'like', '%' . $value . '%');
                continue;
            }

            if (in_array($field, ['nama_debitur', 'status', 'nopen', 'customer_id'], true)) {
                $query->where($field, 'like', '%' . $value . '%');
                continue;
            }

            $query->where($field, $value);
        }

        $banpots = $query->paginate(20)->withQueryString();

        return view('banpot.index', compact('banpots', 'years', 'months', 'banks', 'filters'));
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:1900,2100'],
            'bank' => ['required', 'in:' . implode(',', self::BANKS)],
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        try {
            $preview = $this->banpotExcelService->previewFromUploadedFile($request->file('excel_file'));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage(),
            ], 422);
        }

        if ($preview['rows'] === []) {
            return response()->json([
                'message' => 'File Excel tidak memiliki data baris yang bisa diimpor.',
                'preview' => $preview,
            ], 422);
        }

        return response()->json([
            'message' => 'Preview Banpot berhasil.',
            'preview' => $preview,
            'meta' => [
                'bulan' => (int) $validated['bulan'],
                'tahun' => (int) $validated['tahun'],
                'bank' => $validated['bank'],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:1900,2100'],
            'bank' => ['required', 'in:' . implode(',', self::BANKS)],
            'source_filename' => ['nullable', 'string', 'max:255'],
            'sheet_name' => ['nullable', 'string', 'max:255'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.row_number' => ['required', 'integer', 'min:1'],
            'rows.*.data' => ['required', 'array'],
        ]);

        $batchId = (string) Str::uuid();
        $inserted = 0;

        DB::transaction(function () use ($validated, $batchId, &$inserted): void {
            foreach ($validated['rows'] as $row) {
                $record = $this->buildStructuredRecord(
                    (array) ($row['data'] ?? []),
                    (array) ($row['record'] ?? [])
                );

                Banpot::query()->create([
                    'import_batch_id' => $batchId,
                    'source_filename' => $validated['source_filename'] ?? null,
                    'sheet_name' => $validated['sheet_name'] ?? null,
                    'bulan' => (int) $validated['bulan'],
                    'tahun' => (int) $validated['tahun'],
                    'bank' => $validated['bank'],
                    'row_number' => (int) $row['row_number'],
                    'no' => $record['no'] ?? null,
                    'no_pk' => $record['no_pk'] ?? null,
                    'nopen' => $record['nopen'] ?? null,
                    'norek' => $record['norek'] ?? null,
                    'total' => $record['total'] ?? null,
                    'cabang' => $record['cabang'] ?? null,
                    'status' => $record['status'] ?? null,
                    'plafond' => $record['plafond'] ?? null,
                    'selisih' => $record['selisih'] ?? null,
                    'giro_mitra' => $record['giro_mitra'] ?? null,
                    'keterangan' => $record['keterangan'] ?? null,
                    'customer_id' => $record['customer_id'] ?? null,
                    'nama_debitur' => $record['nama_debitur'] ?? null,
                    'jenis_tagihan' => $record['jenis_tagihan'] ?? null,
                    'pengelola_pensiun' => $record['pengelola_pensiun'] ?? null,
                    'pendebetan_angsuran' => $record['pendebetan_angsuran'] ?? null,
                    'nama_mitra_channeling' => $record['nama_mitra_channeling'] ?? null,
                    'row_data' => $row['data'],
                ]);

                $inserted++;
            }
        });

        return response()->json([
            'message' => 'Banpot berhasil disimpan.',
            'import_batch_id' => $batchId,
            'rows_created' => $inserted,
        ], 201);
    }

    private function buildStructuredRecord(array $rowData, array $record): array
    {
        $resolved = [];

        // Prefer values from preview `record`, but fallback to raw row_data mapping.
        foreach (self::BANPOT_COLUMN_MAP as $excelHeader => $columnName) {
            if ($this->hasValue($record[$columnName] ?? null)) {
                $resolved[$columnName] = trim((string) $record[$columnName]);
                continue;
            }

            $resolved[$columnName] = null;
        }

        foreach ($rowData as $header => $value) {
            $normalizedHeader = $this->normalizeHeaderKey((string) $header);
            $columnName = self::BANPOT_COLUMN_MAP[$normalizedHeader] ?? null;

            if ($columnName === null) {
                continue;
            }

            if (!$this->hasValue($value)) {
                continue;
            }

            $resolved[$columnName] = trim((string) $value);
        }

        return $resolved;
    }

    private function normalizeHeaderKey(string $header): string
    {
        $normalized = preg_replace('/\s+/', ' ', $header);

        return strtoupper(trim($normalized ?? $header));
    }

    private function hasValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        return trim((string) $value) !== '';
    }
}