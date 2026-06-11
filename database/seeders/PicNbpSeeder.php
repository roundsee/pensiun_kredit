<?php

namespace Database\Seeders;

use App\Models\PicNbp;
use Illuminate\Database\Seeder;

class PicNbpSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama_petugas'       => 'Tony Prasetyo',
                'jabatan'            => 'PIC Area Jember',
                'nomor_substitusi'   => 'NBP.01.0030/PIC/II/2026',
                'tanggal_substitusi' => '02/02/2026',
            ],
            [
                'nama_petugas'       => 'Lalu Aji',
                'jabatan'            => 'PIC Area Makassar',
                'nomor_substitusi'   => null,
                'tanggal_substitusi' => null,
            ],
            [
                'nama_petugas'       => 'Riki W',
                'jabatan'            => 'PIC Area Bandung',
                'nomor_substitusi'   => 'NBP.05.0030/PIC/IV/2026',
                'tanggal_substitusi' => '01/04/2026',
            ],
            [
                'nama_petugas'       => 'Tuminar',
                'jabatan'            => 'PIC Area Medan',
                'nomor_substitusi'   => 'NBP.06.0030/PIC/IV/2026',
                'tanggal_substitusi' => '01/05/2026',
            ],
        ];

        foreach ($data as $row) {
            PicNbp::updateOrCreate(
                ['nama_petugas' => $row['nama_petugas'], 'jabatan' => $row['jabatan']],
                $row
            );
        }
    }
}
