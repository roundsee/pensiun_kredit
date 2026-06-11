<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportKbReferenceOptionsCommand extends Command
{
    protected $signature = 'kb:import-reference-options {--path= : Absolute path to Simulasi_KB.xlsx}';

    protected $description = 'Import KB reference options (bank tujuan and area) from Simulasi_KB.xlsx into database';

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
            $bankTujuan = $this->readColumnValues($spreadsheet->getSheetByName('data'), 'Y', 18, 80);
            $area = $this->readAreaValues($spreadsheet->getSheetByName('AREA'));
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        DB::table('kb_reference_options')->whereIn('category', ['bank_tujuan', 'area'])->delete();

        $inserted = 0;
        foreach ($bankTujuan as $index => $value) {
            DB::table('kb_reference_options')->insert([
                'category' => 'bank_tujuan',
                'value' => $value,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $inserted++;
        }

        foreach ($area as $index => $value) {
            DB::table('kb_reference_options')->insert([
                'category' => 'area',
                'value' => $value,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $inserted++;
        }

        $this->info('Import selesai. Total baris tersimpan: ' . $inserted);
        $this->line('bank_tujuan: ' . count($bankTujuan));
        $this->line('area: ' . count($area));

        return self::SUCCESS;
    }

    private function readColumnValues(mixed $sheet, string $column, int $startRow, int $endRow): array
    {
        if ($sheet === null) {
            return [];
        }

        $values = [];
        for ($row = $startRow; $row <= $endRow; $row++) {
            $value = trim((string) $sheet->getCell($column . $row)->getFormattedValue());
            if ($value !== '') {
                $values[$value] = true;
            }
        }

        return array_keys($values);
    }

    private function readAreaValues(mixed $sheet): array
    {
        if ($sheet === null) {
            return [];
        }

        $columns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $values = [];

        foreach ($columns as $column) {
            for ($row = 4; $row <= 40; $row++) {
                $value = trim((string) $sheet->getCell($column . $row)->getFormattedValue());
                if ($value !== '') {
                    $values[$value] = true;
                }
            }
        }

        return array_keys($values);
    }
}
