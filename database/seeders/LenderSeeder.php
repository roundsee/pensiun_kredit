<?php

namespace Database\Seeders;

use App\Models\Lender;
use Illuminate\Database\Seeder;

class LenderSeeder extends Seeder
{
    public function run(): void
    {
        $lenders = [
            ['name' => 'Bank Sahabat UKM', 'share_lender' => 70, 'share_koperasi' => 30],
            ['name' => 'Bank Mandiri', 'share_lender' => 80, 'share_koperasi' => 20],
        ];

        foreach ($lenders as $lender) {
            Lender::query()->updateOrCreate(
                ['name' => $lender['name']],
                $lender
            );
        }
    }
}
