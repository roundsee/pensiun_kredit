<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$d = App\Models\DataSimulasi::with('pelengkap')->find(29);
$data = App\Services\DocumentDataBuilderService::buildSppkData($d);
$html = view('sppk.template', $data + ['is_preview' => false])->render();
$needle = $data['kota_tanggal_surat'] ?? '';
echo (strpos($html, $needle) !== false ? 'FOUND: ' : 'NOT_FOUND: ') . $needle . PHP_EOL;
