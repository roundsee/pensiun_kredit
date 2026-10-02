<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sumber Data Reference
    |--------------------------------------------------------------------------
    |
    | Database tempat tabel reference wilayah (provinsi/kabupaten/kecamatan/desa)
    | sistem lama berada. Tabel `desa` di sana diberi nama `kelurahan` di app ini.
    | Isi tabel reference di-*copy* satu kali saat migration, jadi setelah itu
    | aplikasi tidak lagi bergantung pada database tersebut.
    |
    | Migration aman di-skip kalau database sumber tidak tersedia.
    |
    */

    'reference_source_db' => env('DEBITUR_REFERENCE_SOURCE_DB', 'db_sg'),

    /*
    |--------------------------------------------------------------------------
    | Instansi Pensiun
    |--------------------------------------------------------------------------
    |
    | Tidak ada tabel reference untuk instansi pensiun, jadi nilainya fix di
    | sini supaya mudah ditambah tanpa perlu migration baru.
    |
    */

    'institusi_pensiun' => ['TASPEN', 'ASABRI'],

    /*
    |--------------------------------------------------------------------------
    | Jumlah Baris Per Halaman
    |--------------------------------------------------------------------------
    */

    'per_page' => 15,

];
