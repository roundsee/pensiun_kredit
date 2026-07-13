<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controller = app(App\Http\Controllers\DnkaController::class);
$dataSimulasi = App\Models\DataSimulasi::query()->with('pelengkap')->latest('id')->first();

if (!$dataSimulasi) {
    fwrite(STDERR, "No DataSimulasi found\n");
    exit(1);
}

$invoke = static function (object $object, string $method, array $args = []) {
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($object, $args);
};

$ioFactoryClass = 'PhpOffice\\PhpSpreadsheet\\IOFactory';
$spreadsheetClass = 'PhpOffice\\PhpSpreadsheet\\Spreadsheet';

echo 'DATA_SIMULASI_ID=' . $dataSimulasi->id . PHP_EOL;

$dataRekeningTemplatePath = storage_path('upload/Data_rekening.xlsx');
$permohonanCifTemplatePath = storage_path('upload/permohonan_cif.xlsx');
$pelunasanTemplatePath = storage_path('upload/Pelunasan_TO_KB.xlsx');

$dataRekeningHeaderData = $invoke($controller, 'extractHeaderCellMap', [$dataRekeningTemplatePath]);
$permohonanCifHeaderData = $invoke($controller, 'extractHeaderCellMap', [$permohonanCifTemplatePath]);
$pelunasanHeaderData = $invoke($controller, 'extractHeaderCellMap', [$pelunasanTemplatePath]);

$sheetSpecs = [
    [
        'sheet_name' => 'DNKA VERTIKAL',
        'path' => $invoke($controller, 'createFilledWorkbookFromTemplate', [
            'DNKA_vertical.xlsx',
            $invoke($controller, 'buildVerticalCellValues', [$dataSimulasi]),
            ['I17', 'I18'],
            false,
            'bundle_dnka_vertical_debug',
        ]),
    ],
    [
        'sheet_name' => 'DNKA HORIZONTAL',
        'path' => $invoke($controller, 'createFilledWorkbookFromTemplate', [
            'DNKA_Horizontal.xlsx',
            $invoke($controller, 'buildHorizontalCellValues', [$dataSimulasi]),
            [],
            false,
            'bundle_dnka_horizontal_debug',
        ]),
    ],
    [
        'sheet_name' => 'DATA LOS BULK',
        'path' => $invoke($controller, 'createFilledWorkbookFromTemplate', [
            'Data_Los_bulk.xlsx',
            $invoke($controller, 'buildDataLosBulkCellValues', [$dataSimulasi]),
            $invoke($controller, 'buildDataLosBulkForceOverrideCells'),
            true,
            'bundle_data_los_bulk_debug',
        ]),
    ],
    [
        'sheet_name' => 'REPAYMENT SCHEDULE',
        'path' => $invoke($controller, 'createFilledWorkbookFromTemplate', [
            'Repayment_Schedule.xlsx',
            $invoke($controller, 'buildRepaymentScheduleCellValues', [$dataSimulasi]),
            [],
            false,
            'bundle_repayment_schedule_debug',
        ]),
    ],
    [
        'sheet_name' => 'DATA REKENING',
        'path' => $invoke($controller, 'createFilledWorkbookFromTemplate', [
            'Data_rekening.xlsx',
            $invoke($controller, 'buildDataRekeningHeaderMappedCellValues', [
                $dataRekeningHeaderData['header_map'] ?? [],
                (($dataRekeningHeaderData['header_row'] ?? 1) + 1),
                $dataSimulasi,
            ]),
            [],
            false,
            'bundle_data_rekening_debug',
        ]),
    ],
    [
        'sheet_name' => 'PERMOHONAN CIF',
        'path' => $invoke($controller, 'createFilledWorkbookFromTemplate', [
            'permohonan_cif.xlsx',
            $invoke($controller, 'buildPermohonanCifHeaderMappedCellValues', [
                $permohonanCifHeaderData['header_map'] ?? [],
                (($permohonanCifHeaderData['header_row'] ?? 1) + 1),
                $dataSimulasi,
            ]),
            [],
            false,
            'bundle_permohonan_cif_debug',
        ]),
    ],
    [
        'sheet_name' => 'PELUNASAN TO KB',
        'path' => $invoke($controller, 'createFilledWorkbookFromTemplate', [
            'Pelunasan_TO_KB.xlsx',
            $invoke($controller, 'buildPelunasanToKbHeaderMappedCellValues', [
                $pelunasanHeaderData['header_map'] ?? [],
                (($pelunasanHeaderData['header_row'] ?? 1) + 1),
                $dataSimulasi,
            ]),
            [],
            false,
            'bundle_pelunasan_to_kb_debug',
        ]),
    ],
];

$bundleSpreadsheet = new $spreadsheetClass();
$bundleSpreadsheet->removeSheetByIndex(0);

foreach ($sheetSpecs as $spec) {
    echo 'CHECK=' . $spec['sheet_name'] . ';PATH=' . $spec['path'] . PHP_EOL;

    try {
        $sourceSpreadsheet = $ioFactoryClass::load($spec['path']);
        $sheetCount = $sourceSpreadsheet->getSheetCount();
        $titles = [];

        for ($index = 0; $index < $sheetCount; $index++) {
            $titles[] = $sourceSpreadsheet->getSheet($index)->getTitle();
        }

        echo 'LOADED=' . $spec['sheet_name'] . ';COUNT=' . $sheetCount . ';TITLES=' . implode('|', $titles) . PHP_EOL;

        $sourceSheet = $sourceSpreadsheet->getSheet(0);
        $bundleSpreadsheet->addExternalSheet($sourceSheet);
        $newSheet = $bundleSpreadsheet->getSheet($bundleSpreadsheet->getSheetCount() - 1);
        $newSheet->setTitle($spec['sheet_name']);
        echo 'ADDED=' . $spec['sheet_name'] . PHP_EOL;

        $sourceSpreadsheet->disconnectWorksheets();
        unset($sourceSpreadsheet);
    } catch (Throwable $e) {
        echo 'FAILED=' . $spec['sheet_name'] . ';MESSAGE=' . $e->getMessage() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
        exit(2);
    }
}

try {
    $tempDir = storage_path('app/temp');
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    $bundlePath = $tempDir . '/final_bundle_debug_' . time() . '.xlsx';
    $bundleWriter = $ioFactoryClass::createWriter($bundleSpreadsheet, 'Xlsx');
    $bundleWriter->setPreCalculateFormulas(false);
    $bundleWriter->save($bundlePath);
    $bundleSpreadsheet->disconnectWorksheets();
    unset($bundleSpreadsheet);
    echo 'BUNDLE_SAVE_OK;PATH=' . $bundlePath . PHP_EOL;
} catch (Throwable $e) {
    echo 'BUNDLE_SAVE_FAILED' . PHP_EOL;
    echo 'EXCEPTION=' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(3);
}
