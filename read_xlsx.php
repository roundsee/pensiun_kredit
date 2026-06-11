<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('storage/upload/DNKA_horizontal.xlsx');
foreach ($spreadsheet->getAllSheets() as $sheet) {
    echo 'Sheet: ' . $sheet->getTitle() . PHP_EOL;
    $rows = $sheet->toArray(null, true, true, true);
    $count = 0;
    foreach ($rows as $rowIdx => $row) {
        if ($count >= 10) break;
        echo 'Row ' . $rowIdx . ': ' . json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $count++;
    }
    echo PHP_EOL;
}
