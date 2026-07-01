<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = $app->make(App\Services\KbSimulationExcelService::class);

$input = [
    'produk' => 'Platinum',
    'jenis_pensiun' => 'Sendiri',
    'bank_tujuan' => 'KB',
    'tanggal_simulasi' => date('Y-m-d'),
    'tanggal_lahir' => '1960-01-01',
    'instansi' => 'TASPEN',
    'gaji_pensiun' => 2057500,
    'angsuran_lainnya' => 0,
    'tenor' => 100,
    'rate_percent_override' => 16,
    'admin_angsuran_percent_override' => 0,
    'blokir_angsuran' => 1,
    'pelunasan' => 0,
];

$resultBefore = $svc->calculate($input);
$input['plafond'] = round((float) ($resultBefore['plafond_max'] ?? 0));
$resultAfter = $svc->calculate($input);

$plafondMax = (float) ($resultAfter['plafond_max'] ?? 0);
$plafondDisplayed = round($plafondMax);
$plafondTolerance = 1000.0;
$plafondValid = ((float) $input['plafond']) <= ($plafondDisplayed + $plafondTolerance);

$output = [
    'plafond_max_initial' => $resultBefore['plafond_max'] ?? null,
    'plafond_input' => $input['plafond'],
    'plafond_max_recalc' => $plafondMax,
    'plafond_max_displayed' => $plafondDisplayed,
    'plafond_valid' => $plafondValid,
    'total_angsuran' => $resultAfter['total_angsuran'] ?? null,
    'sisa_gaji_akhir' => $resultAfter['sisa_gaji_akhir'] ?? null,
    'angsuran' => $resultAfter['angsuran'] ?? null,
    'biaya_adm_angs' => $resultAfter['biaya_adm_angs'] ?? null,
];

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
