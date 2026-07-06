<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = App\Models\DataSimulasi::with('pelengkap')->find(29);
$data = App\Services\DocumentDataBuilderService::buildSiTakeOverData($d);
$html = view('si.template_to', $data)->render();

echo (strpos($html, '...............................') === false ? 'NO_DOTS' : 'HAS_DOTS') . PHP_EOL;
echo (strpos($html, 'No</td><td>:</td><td>' . ($data['nomor_si'] ?? '')) !== false ? 'HAS_NOMOR_SI' : 'NO_NOMOR_SI') . PHP_EOL;
