<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InsuranceRate;

class InsuranceRateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            144 => 378.98,
            132 => 335.29,
            120 => 296.64,
            108 => 265.97,
            96  => 235.31,
            84  => 204.65,
            72  => 173.98,
            60  => 143.32,
            48  => 112.66,
            36  => 81.99,
            24  => 51.33,
            12  => 28.58,
        ];

        foreach ($rows as $tenor => $premium) {
            InsuranceRate::updateOrCreate([
                'product' => 'Platinum',
                'tenor' => (int) $tenor,
            ], [
                'premium_per_million' => (float) $premium,
            ]);
        }
    }
}
