<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportKbProductStructCommand extends Command
{
    protected $signature = 'kb:import-product-struct {--path= : Absolute path to Simulasi_KB.xlsx}';

    protected $description = 'Import named range SETTING from Simulasi_KB.xlsx into table product_struct';

    public function handle(): int
    {
        $ioFactoryClass = 'PhpOffice\\PhpSpreadsheet\\IOFactory';

        if (!class_exists($ioFactoryClass)) {
            $this->error('PhpSpreadsheet belum tersedia. Jalankan composer install dan pastikan dependency terpasang.');
            return self::FAILURE;
        }

        $path = (string) ($this->option('path') ?: storage_path('upload/Simulasi_KB.xlsx'));

        if (!is_file($path)) {
            $this->error('File Excel tidak ditemukan: ' . $path);
            return self::FAILURE;
        }

        $spreadsheet = $ioFactoryClass::load($path);

        try {
            $namedRange = $spreadsheet->getNamedRange('SETTING');
            if ($namedRange === null) {
                $this->error('Named range SETTING tidak ditemukan di workbook.');
                return self::FAILURE;
            }

            $sheet = $namedRange->getWorksheet();
            if ($sheet === null) {
                $this->error('Worksheet untuk named range SETTING tidak ditemukan.');
                return self::FAILURE;
            }

            $range = $namedRange->getRange();
            if (str_contains($range, '!')) {
                $range = explode('!', $range, 2)[1];
            }
            $range = str_replace('$', '', $range);

            $rows = $sheet->rangeToArray($range, null, true, false, false);
            if (count($rows) < 2) {
                $this->error('Data named range SETTING kosong.');
                return self::FAILURE;
            }

            array_shift($rows); // Header row

            DB::table('product_struct')->truncate();

            $inserted = 0;
            foreach ($rows as $index => $row) {
                $produk = trim((string) ($row[0] ?? ''));
                if ($produk === '') {
                    continue;
                }

                DB::table('product_struct')->insert([
                    'produk' => $produk,
                    'plafond_min' => $this->toNumber($row[1] ?? null),
                    'plafond_max' => $this->toNumber($row[2] ?? null),
                    'tenor_max' => $this->toInt($row[3] ?? null),
                    'rate_percent' => $this->toNumber($row[4] ?? null),
                    'provisi_percent' => $this->toNumber($row[5] ?? null),
                    'usia_masuk_min' => $this->toInt($row[6] ?? null),
                    'usia_max' => $this->toInt($row[7] ?? null),
                    'admin_percent' => $this->toNumber($row[8] ?? null),
                    'blokir_angsuran' => $this->toInt($row[9] ?? null),
                    'taspen' => $this->toNumber($row[10] ?? null),
                    'tata_laksana' => $this->toNumber($row[11] ?? null),
                    'tata_laksana_plus_percent' => $this->toNumber($row[12] ?? null),
                    'admin_angsuran_percent' => $this->toNumber($row[13] ?? null),
                    'dbr_percent' => $this->toNumber($row[14] ?? null),
                    'asabri' => $this->toNumber($row[15] ?? null),
                    'usia_masuk_max' => $this->toInt($row[16] ?? null),
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $inserted++;
            }

            $this->info('Import product_struct selesai. Total baris: ' . $inserted);

            return self::SUCCESS;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function toInt(mixed $value): ?int
    {
        $number = $this->toNumber($value);
        return $number === null ? null : (int) round($number);
    }

    private function toNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9,.-]/', '', (string) $value);
        if ($clean === null || $clean === '') {
            return null;
        }

        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }
}
