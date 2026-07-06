<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$d = App\Models\DataSimulasi::with('pelengkap')->find(29);
if (!$d) {
    echo "NOT_FOUND\n";
    exit;
}
$data = App\Services\DocumentDataBuilderService::buildSppkData($d);
echo json_encode([
    'tanggal_surat' => $data['tanggal_surat'] ?? null,
    'kota_tanggal_surat' => $data['kota_tanggal_surat'] ?? null,
    'kota_ttd' => $data['kota_ttd'] ?? null,
    'nomor_sppk' => $data['nomor_sppk'] ?? null,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
