<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NominatifExcelService
{
    public function previewFromUploadedFile(UploadedFile $file): array
    {
        $storedPath = $file->store('upload/nominatif');

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

            if ($highestRow < 7) {
                return [
                    'source_filename' => $sourceFilename,
                    'sheet_name' => $sheetName,
                    'headers' => [],
                    'rows' => [],
                ];
            }

            // Header referensi ada di row 6 (main) dan row 7 (sub).
            $matrix = $sheet->rangeToArray('A6:' . $highestColumn . $highestRow, null, true, false, false);

            if ($matrix === [] || count($matrix) < 2) {
                return [
                    'source_filename' => $sourceFilename,
                    'sheet_name' => $sheetName,
                    'headers' => [],
                    'rows' => [],
                ];
            }

            $headerRow = $matrix[0];
            $subHeaderRow = $matrix[1];
            $headers = $this->buildHeaders($headerRow, $subHeaderRow);

            $rows = [];
            foreach (array_slice($matrix, 2) as $index => $values) {
                $rowData = [];
                $record = [];
                $hasValue = false;

                foreach ($headers as $columnIndex => $header) {
                    if ($header === null) {
                        continue;
                    }

                    $value = $this->normalizeCellValue($values[$columnIndex] ?? null);
                    $rowData[$header['label']] = $value;

                    if ($value !== null && $value !== '') {
                        $hasValue = true;
                    }

                    if ($header['field'] !== null) {
                        $record[$header['field']] = $value;
                    }
                }

                if (!$hasValue) {
                    continue;
                }

                $rows[] = [
                    'row_number' => $index + 8,
                    'data' => $rowData,
                    'record' => $record,
                ];
            }

            return [
                'source_filename' => $sourceFilename,
                'sheet_name' => $sheetName,
                'headers' => array_values(array_map(static fn ($h) => $h['label'], array_filter($headers))),
                'rows' => $rows,
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function buildHeaders(array $main, array $sub): array
    {
        $fieldMap = [
            1 => ['NO.', 'no'],
            2 => ['NOPEN', 'nopen'],
            3 => ['NOREK', 'norek'],
            4 => ['TANGGAL', 'tanggal'],
            5 => ['NAMA', 'nama'],
            6 => ['MO', 'mo'],
            7 => ['PLAFOND', 'plafond'],
            8 => ['BUNGA', 'bunga'],
            9 => ['KE-', 'angsuran_ke'],
            10 => ['JW', 'jw'],
            11 => ['% byr', 'persen_byr'],
            12 => ['BAKI AWAL', 'baki_awal'],
            13 => ['ANGSURAN JATUH TEMPO POKOK', 'jt_pokok'],
            14 => ['ANGSURAN JATUH TEMPO BUNGA', 'jt_bunga'],
            15 => ['ANGSURAN JATUH TEMPO S.WJB', 'jt_swjb'],
            16 => ['ANGSURAN JATUH TEMPO JUMLAH', 'jt_jumlah'],
            17 => ['ANGSURAN BELUM DITERIMA POKOK', 'blm_pokok'],
            18 => ['ANGSURAN BELUM DITERIMA BUNGA', 'blm_bunga'],
            19 => ['ANGSURAN BELUM DITERIMA S.WJB', 'blm_swjb'],
            20 => ['ANGSURAN BELUM DITERIMA JUMLAH', 'blm_jumlah'],
            21 => ['ANGSURAN TELAH DITERIMA POKOK', 'terima_pokok'],
            22 => ['ANGSURAN TELAH DITERIMA BUNGA', 'terima_bunga'],
            23 => ['ANGSURAN TELAH DITERIMA S.WJB', 'terima_swjb'],
            24 => ['ANGSURAN TELAH DITERIMA JUMLAH', 'terima_jumlah'],
            25 => ['BAKI AKHIR', 'baki_akhir'],
        ];

        $headers = [];
        $max = max(count($main), count($sub));

        for ($i = 0; $i < $max; $i++) {
            if (!array_key_exists($i, $fieldMap)) {
                $headers[$i] = null;
                continue;
            }

            [$defaultLabel, $field] = $fieldMap[$i];
            $m = trim((string) ($main[$i] ?? ''));
            $s = trim((string) ($sub[$i] ?? ''));

            $label = $defaultLabel;
            if ($m !== '' && $s !== '') {
                $label = $m . ' ' . $s;
            } elseif ($m !== '') {
                $label = $m;
            }

            $headers[$i] = [
                'label' => $label,
                'field' => $field,
            ];
        }

        return $headers;
    }

    private function normalizeCellValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}