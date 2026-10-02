<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | Token statis yang dipakai Google Apps Script saat memanggil endpoint
    | sync pencairan platinum. Disimpan di .env (bukan di repo) supaya token
    | tidak ikut ter-commit.
    |
    | Generate token baru dengan:
    |   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
    |
    */

    'api_token' => env('PENCAIRAN_PLATINUM_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Batas Payload
    |--------------------------------------------------------------------------
    |
    | Batas jumlah baris per request. Apps Script mengirim per batch, bukan
    | seluruh sheet sekaligus, supaya tidak ada request yang terlalu besar
    | dan Google tidak deem sebagai penyalahgunaan.
    |
    */

    'max_rows_per_request' => (int) env('PENCAIRAN_PLATINUM_MAX_ROWS', 200),

];