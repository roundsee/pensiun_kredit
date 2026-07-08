<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BanpotExcelService
{
    private const COLUMN_MAP = [
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

    public function previewFromUploadedFile(UploadedFile $file): array
    {
        $storedPath = $file->store('upload/banpot');

        try {
            return $this->previewFromPath(storage_path('app/' . $storedPath), $file->getClientOriginalName());
        } finally {
            Storage::delete($storedPath);
        }
    }

    public function previewFromPath(string $absolutePath, ?string $sourceFilename = null): array
    {
        $ioFactoryClass = 'PhpOffice\\PhpSpreadsheet\\IOFactory';

        if (!class_exists($ioFactoryClass)) {
            throw new \RuntimeException('PhpSpreadsheet belum tersedia di server.');
        }

        $spreadsheet = $ioFactoryClass::load($absolutePath);

        try {
            $sheet = $spreadsheet->getActiveSheet();
            $sheetName = $sheet->getTitle();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();

            if ($highestRow < 1) {
                return [
                    'source_filename' => $sourceFilename,
                    'sheet_name' => $sheetName,
                    'headers' => [],
                    'rows' => [],
                ];
            }

            $matrix = $sheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, false, false);

            if ($matrix === []) {
                return [
                    'source_filename' => $sourceFilename,
                    'sheet_name' => $sheetName,
                    'headers' => [],
                    'rows' => [],
                ];
            }

            $rawHeaders = array_map(
                static fn ($value) => trim((string) $value),
                $matrix[0] ?? []
            );
            $headers = $this->normalizeHeaders($rawHeaders);

            $rows = [];
            foreach (array_slice($matrix, 1) as $rowIndex => $values) {
                $rowData = [];
                $hasValue = false;
                $record = [];

                foreach ($headers as $columnIndex => $header) {
                    $value = $values[$columnIndex] ?? null;
                    $normalizedValue = $this->normalizeCellValue($value);

                    if ($normalizedValue !== null && $normalizedValue !== '') {
                        $hasValue = true;
                    }

                    $rowData[$header] = $normalizedValue;

                    $mappedColumn = self::COLUMN_MAP[$this->normalizeHeaderKey($header)] ?? null;
                    if ($mappedColumn !== null) {
                        $record[$mappedColumn] = $normalizedValue;
                    }
                }

                if (!$hasValue) {
                    continue;
                }

                $rows[] = [
                    'row_number' => $rowIndex + 2,
                    'data' => $rowData,
                    'record' => $record,
                ];
            }

            return [
                'source_filename' => $sourceFilename,
                'sheet_name' => $sheetName,
                'headers' => $headers,
                'rows' => $rows,
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $index => $header) {
            $label = $header !== '' ? $header : 'Column ' . ($index + 1);

            if (!array_key_exists($label, $normalized)) {
                $normalized[$label] = 1;
                continue;
            }

            $normalized[$label]++;
            $label = $label . ' (' . $normalized[$label] . ')';
            $normalized[$label] = 1;
        }

        return array_keys($normalized);
    }

    private function normalizeCellValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function normalizeHeaderKey(string $header): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $header) ?? $header));
    }
}