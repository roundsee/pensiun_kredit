<?php

namespace App\Http\Controllers;

use App\Models\Nominatif;
use App\Services\NominatifExcelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NominatifController extends Controller
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

    public function __construct(private readonly NominatifExcelService $nominatifExcelService)
    {
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

        return view('nominatif.import_initial', compact('years', 'months', 'banks'));
    }

    public function previewInitial(Request $request): JsonResponse
    {
        $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:1900,2100'],
            'bank' => ['required', 'in:' . implode(',', self::BANKS)],
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        try {
            $preview = $this->nominatifExcelService->previewFromUploadedFile($request->file('excel_file'));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membaca file initial nominatif: ' . $e->getMessage(),
            ], 422);
        }

        if ($preview['rows'] === []) {
            return response()->json([
                'message' => 'Tidak ada data nominatif yang terdeteksi di file.',
                'preview' => $preview,
            ], 422);
        }

        return response()->json([
            'message' => 'Preview initial nominatif berhasil.',
            'preview' => $preview,
        ]);
    }

    public function storeInitial(Request $request): JsonResponse
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
            'rows.*.record' => ['nullable', 'array'],
        ]);

        $batchId = (string) Str::uuid();
        $inserted = 0;

        DB::transaction(function () use ($validated, $batchId, &$inserted): void {
            Nominatif::query()
                ->where('tahun', (int) $validated['tahun'])
                ->where('bulan', (int) $validated['bulan'])
                ->where('bank', $validated['bank'])
                ->delete();

            foreach ($validated['rows'] as $row) {
                $record = (array) ($row['record'] ?? []);

                Nominatif::query()->create([
                    'import_batch_id' => $batchId,
                    'source_filename' => $validated['source_filename'] ?? null,
                    'sheet_name' => $validated['sheet_name'] ?? null,
                    'bulan' => (int) $validated['bulan'],
                    'tahun' => (int) $validated['tahun'],
                    'bank' => $validated['bank'],
                    'row_number' => (int) $row['row_number'],
                    'no' => $record['no'] ?? null,
                    'nopen' => $record['nopen'] ?? null,
                    'norek' => $record['norek'] ?? null,
                    'tanggal' => $record['tanggal'] ?? null,
                    'nama' => $record['nama'] ?? null,
                    'mo' => $record['mo'] ?? null,
                    'plafond' => $record['plafond'] ?? null,
                    'bunga' => $record['bunga'] ?? null,
                    'angsuran_ke' => $record['angsuran_ke'] ?? null,
                    'jw' => $record['jw'] ?? null,
                    'persen_byr' => $record['persen_byr'] ?? null,
                    'baki_awal' => $record['baki_awal'] ?? null,
                    'jt_pokok' => $record['jt_pokok'] ?? null,
                    'jt_bunga' => $record['jt_bunga'] ?? null,
                    'jt_swjb' => $record['jt_swjb'] ?? null,
                    'jt_jumlah' => $record['jt_jumlah'] ?? null,
                    'blm_pokok' => $record['blm_pokok'] ?? null,
                    'blm_bunga' => $record['blm_bunga'] ?? null,
                    'blm_swjb' => $record['blm_swjb'] ?? null,
                    'blm_jumlah' => $record['blm_jumlah'] ?? null,
                    'terima_pokok' => $record['terima_pokok'] ?? null,
                    'terima_bunga' => $record['terima_bunga'] ?? null,
                    'terima_swjb' => $record['terima_swjb'] ?? null,
                    'terima_jumlah' => $record['terima_jumlah'] ?? null,
                    'baki_akhir' => $record['baki_akhir'] ?? null,
                    'row_data' => $row['data'],
                ]);

                $inserted++;
            }
        });

        return response()->json([
            'message' => 'Initial nominatif berhasil disimpan.',
            'import_batch_id' => $batchId,
            'rows_created' => $inserted,
        ], 201);
    }
}