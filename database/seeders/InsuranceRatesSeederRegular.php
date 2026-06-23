<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsuranceRatesSeederRegular extends Seeder
{
/**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel terlebih dahulu agar tidak duplikat jika dijalankan ulang
        $data = [
            // Usia 49
            ['product' => 'regular', 'tenor' => 1, 'usia' => 49, 'premium_per_million' => 3.55],
            ['product' => 'regular', 'tenor' => 2, 'usia' => 49, 'premium_per_million' => 7.68],
            ['product' => 'regular', 'tenor' => 3, 'usia' => 49, 'premium_per_million' => 12.17],
            ['product' => 'regular', 'tenor' => 4, 'usia' => 49, 'premium_per_million' => 16.99],
            ['product' => 'regular', 'tenor' => 5, 'usia' => 49, 'premium_per_million' => 22.15],
            ['product' => 'regular', 'tenor' => 6, 'usia' => 49, 'premium_per_million' => 27.64],
            ['product' => 'regular', 'tenor' => 7, 'usia' => 49, 'premium_per_million' => 33.38],
            ['product' => 'regular', 'tenor' => 8, 'usia' => 49, 'premium_per_million' => 39.45],
            ['product' => 'regular', 'tenor' => 9, 'usia' => 49, 'premium_per_million' => 45.76],
            ['product' => 'regular', 'tenor' => 10, 'usia' => 49, 'premium_per_million' => 52.26],
            ['product' => 'regular', 'tenor' => 11, 'usia' => 49, 'premium_per_million' => 58.89],
            ['product' => 'regular', 'tenor' => 12, 'usia' => 49, 'premium_per_million' => 65.58],
            ['product' => 'regular', 'tenor' => 13, 'usia' => 49, 'premium_per_million' => 72.30],
            ['product' => 'regular', 'tenor' => 14, 'usia' => 49, 'premium_per_million' => 79.00],
            ['product' => 'regular', 'tenor' => 15, 'usia' => 49, 'premium_per_million' => 85.63],

            // Usia 59
            ['product' => 'regular', 'tenor' => 1, 'usia' => 59, 'premium_per_million' => 7.48],
            ['product' => 'regular', 'tenor' => 2, 'usia' => 59, 'premium_per_million' => 15.90],
            ['product' => 'regular', 'tenor' => 3, 'usia' => 59, 'premium_per_million' => 24.60],
            ['product' => 'regular', 'tenor' => 4, 'usia' => 59, 'premium_per_million' => 33.53],
            ['product' => 'regular', 'tenor' => 5, 'usia' => 59, 'premium_per_million' => 42.63],
            ['product' => 'regular', 'tenor' => 6, 'usia' => 59, 'premium_per_million' => 51.80],
            ['product' => 'regular', 'tenor' => 7, 'usia' => 59, 'premium_per_million' => 61.16],
            ['product' => 'regular', 'tenor' => 8, 'usia' => 59, 'premium_per_million' => 70.61],
            ['product' => 'regular', 'tenor' => 9, 'usia' => 59, 'premium_per_million' => 80.16],
            ['product' => 'regular', 'tenor' => 10, 'usia' => 59, 'premium_per_million' => 89.78],
            ['product' => 'regular', 'tenor' => 11, 'usia' => 59, 'premium_per_million' => 99.46],
            ['product' => 'regular', 'tenor' => 12, 'usia' => 59, 'premium_per_million' => 109.16],
            ['product' => 'regular', 'tenor' => 13, 'usia' => 59, 'premium_per_million' => 118.89],
            ['product' => 'regular', 'tenor' => 14, 'usia' => 59, 'premium_per_million' => 128.61],
            ['product' => 'regular', 'tenor' => 15, 'usia' => 59, 'premium_per_million' => 138.29],

            // Usia 69
            ['product' => 'regular', 'tenor' => 1, 'usia' => 69, 'premium_per_million' => 10.83],
            ['product' => 'regular', 'tenor' => 2, 'usia' => 69, 'premium_per_million' => 23.14],
            ['product' => 'regular', 'tenor' => 3, 'usia' => 69, 'premium_per_million' => 36.06],
            ['product' => 'regular', 'tenor' => 4, 'usia' => 69, 'premium_per_million' => 49.57],
            ['product' => 'regular', 'tenor' => 5, 'usia' => 69, 'premium_per_million' => 63.60],
            ['product' => 'regular', 'tenor' => 6, 'usia' => 69, 'premium_per_million' => 77.99],
            ['product' => 'regular', 'tenor' => 7, 'usia' => 69, 'premium_per_million' => 92.93],
            ['product' => 'regular', 'tenor' => 8, 'usia' => 69, 'premium_per_million' => 108.41],
            ['product' => 'regular', 'tenor' => 9, 'usia' => 69, 'premium_per_million' => 124.65],
            ['product' => 'regular', 'tenor' => 10, 'usia' => 69, 'premium_per_million' => 141.71],
            ['product' => 'regular', 'tenor' => 11, 'usia' => 69, 'premium_per_million' => 159.66],
            ['product' => 'regular', 'tenor' => 12, 'usia' => 69, 'premium_per_million' => 178.67],
            ['product' => 'regular', 'tenor' => 13, 'usia' => 69, 'premium_per_million' => 198.98],
            ['product' => 'regular', 'tenor' => 14, 'usia' => 69, 'premium_per_million' => 220.68],
            ['product' => 'regular', 'tenor' => 15, 'usia' => 69, 'premium_per_million' => 243.81],

            // Usia 74
            ['product' => 'regular', 'tenor' => 1, 'usia' => 74, 'premium_per_million' => 20.66],
            ['product' => 'regular', 'tenor' => 2, 'usia' => 74, 'premium_per_million' => 51.33],
            ['product' => 'regular', 'tenor' => 3, 'usia' => 74, 'premium_per_million' => 81.99],
            ['product' => 'regular', 'tenor' => 4, 'usia' => 74, 'premium_per_million' => 112.66],
            ['product' => 'regular', 'tenor' => 5, 'usia' => 74, 'premium_per_million' => 143.32],
        ];

        // Opsional: Tambahkan timestamps created_at dan updated_at secara otomatis
        $now = now();
        $data = array_map(function ($row) use ($now) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            return $row;
        }, $data);

        // Eksekusi insert massal ke database
        DB::table('insurance_rates')->insert($data);
    }
}
