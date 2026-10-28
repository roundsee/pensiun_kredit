<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = new App\Services\KbSimulationExcelService();
$r = $svc->calculate([
    'produk' => 'Platinum',
    'jenis_pensiun' => 'Sendiri',
    'bank_tujuan' => 'KB',
    'tanggal_simulasi' => '2026-08-27',
    'tanggal_lahir' => '1956-06-02',
    'gaji_pensiun' => 5000000,
    'angsuran_lainnya' => 1500000,
    'tenor' => 60,
    'plafond' => 200000000,
    'extra_premi' => 100000,
]);
var_dump($r['extra_premi'], $r['total_biaya'], $r['terima_bersih']);
