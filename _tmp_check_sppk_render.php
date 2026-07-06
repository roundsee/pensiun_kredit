<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = App\Models\DataSimulasi::with('pelengkap')->find(29);
$data = App\Services\DocumentDataBuilderService::buildSppkData($d);
$html = view('sppk.template', $data + ['is_preview' => true])->render();

preg_match('/<div style="text-align: right; margin-bottom: 4px;">\s*(.*?)\s*<\/div>/s', $html, $m);
echo "kota_field=" . ($data['kota'] ?? '-') . PHP_EOL;
echo "tanggal_sppk_field=" . ($data['tanggal_sppk'] ?? '-') . PHP_EOL;
echo "kota_tanggal_field=" . ($data['kota_tanggal_surat'] ?? '-') . PHP_EOL;
echo "header_div=" . (($m[1] ?? 'NOT_FOUND')) . PHP_EOL;
