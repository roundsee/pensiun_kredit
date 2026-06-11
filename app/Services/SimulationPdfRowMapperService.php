<?php

namespace App\Services;

use Illuminate\Support\Str;

class SimulationPdfRowMapperService
{
    /**
     * Map plain lines into label/value rows for simulation_fields.
     */
    public function mapLines(array $lines): array
    {
        $knownLabels = [
            'Produk',
            'Jenis Pensiun',
            'Mutasi',
            'Bank Asal',
            'Bank Tujuan',
            'Nama Debitur',
            'Tanggal Lahir',
            'Umur',
            'Nomor Pensiun',
            'Instansi',
            'Gaji Pensiun',
            'Sisa Gaji saat pengajuan',
            'Tenor Max',
            'Plafond Max',
            'Tenor',
            'Plafond',
            'Blokir Angsuran',
            'ANGSURAN',
            'Biaya Adm Angs',
            'Total Angsuran',
            'PROVISI',
            'ADMINISTRASI',
            'ASURANSI',
            'Extra Premi',
            'BLOKIR ANGSURAN',
            'PELUNASAN',
            'TATA LAKSANA',
            'Nama Marketing',
            'Kode Area',
            'USIA LUNAS',
            'TGL PERMOHONAN',
            'TGL LUNAS',
            'TOTAL BIAYA',
            'SISA GAJI AKHIR',
            'TERIMA BERSIH',
        ];

        usort($knownLabels, static fn ($a, $b) => strlen($b) <=> strlen($a));

        $section = 'general';
        $mapped = [];

        foreach ($lines as $lineOrder => $line) {
            if (strcasecmp(trim($line), 'RINCIAN PEMBIAYAAN') === 0) {
                $section = 'rincian_pembiayaan';
                continue;
            }

            $label = null;
            $value = null;

            foreach ($knownLabels as $knownLabel) {
                if (Str::startsWith($line, $knownLabel)) {
                    $label = $knownLabel;
                    $value = trim(substr($line, strlen($knownLabel)));
                    break;
                }
            }

            if ($label === null) {
                if (preg_match('/^(.+?)\s{2,}(.+)$/u', $line, $m)) {
                    $label = trim($m[1]);
                    $value = trim($m[2]);
                } elseif (preg_match('/^(.+?)\t+(.+)$/u', $line, $m)) {
                    $label = trim($m[1]);
                    $value = trim($m[2]);
                } else {
                    continue;
                }
            }

            if ($value === '') {
                $value = null;
            }

            $mapped[] = [
                'field_key' => Str::snake($label),
                'field_label' => $label,
                'field_value' => $value,
                'section' => $section,
                'line_order' => $lineOrder + 1,
                'raw_line' => $line,
            ];
        }

        return $mapped;
    }
}
