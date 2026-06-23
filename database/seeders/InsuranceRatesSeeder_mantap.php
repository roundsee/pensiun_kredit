<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsuranceRatesSeeder_mantap extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
$rates = [
            ['product' => 'Platinum', 'tenor' => 120, 'bank_tujuan' => 'MANTAP', 'premium_per_million' => 22.00],
            ['product' => 'Platinum', 'tenor' => 108, 'bank_tujuan' => 'MANTAP', 'premium_per_million' => 22.00],
            ['product' => 'Platinum', 'tenor' => 96,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 22.00],
            ['product' => 'Platinum', 'tenor' => 84,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 21.00],
            ['product' => 'Platinum', 'tenor' => 72,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 19.40],
            ['product' => 'Platinum', 'tenor' => 60,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 16.30],
            ['product' => 'Platinum', 'tenor' => 48,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 13.26],
            ['product' => 'Platinum', 'tenor' => 36,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 10.19],
            ['product' => 'Platinum', 'tenor' => 24,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 7.13],
            ['product' => 'Platinum', 'tenor' => 12,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 4.85],

            ['product' => 'Regular', 'tenor' => 120, 'bank_tujuan' => 'MANTAP', 'premium_per_million' => 22.00],
            ['product' => 'Regular', 'tenor' => 108, 'bank_tujuan' => 'MANTAP', 'premium_per_million' => 22.00],
            ['product' => 'Regular', 'tenor' => 96,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 22.00],
            ['product' => 'Regular', 'tenor' => 84,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 21.00],
            ['product' => 'Regular', 'tenor' => 72,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 19.40],
            ['product' => 'Regular', 'tenor' => 60,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 16.30],
            ['product' => 'Regular', 'tenor' => 48,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 13.26],
            ['product' => 'Regular', 'tenor' => 36,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 10.19],
            ['product' => 'Regular', 'tenor' => 24,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 7.13],
            ['product' => 'Regular', 'tenor' => 12,  'bank_tujuan' => 'MANTAP', 'premium_per_million' => 4.85],            
        ];

        DB::table('insurance_rates')->insert($rates);
    }
}
