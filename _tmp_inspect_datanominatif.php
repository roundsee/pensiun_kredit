<?php
$path = __DIR__ . '/storage/upload/datanominatif.xlsx';
$zip = new ZipArchive();
if ($zip->open($path) !== true) { echo "open failed\n"; exit(1); }
$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
$sharedXml = $zip->getFromName('xl/sharedStrings.xml');
$zip->close();
$shared = [];
if ($sharedXml !== false) {
    $dom = new DOMDocument();
    $dom->loadXML($sharedXml);
    $xp = new DOMXPath($dom);
    $xp->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    foreach ($xp->query('//m:si') as $si) {
        $parts = [];
        foreach ($xp->query('.//m:t', $si) as $t) { $parts[] = $t->textContent; }
        $shared[] = implode('', $parts);
    }
}
$dom = new DOMDocument();
$dom->loadXML($sheetXml);
$xp = new DOMXPath($dom);
$xp->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
for ($r = 1; $r <= 6; $r++) {
    foreach ($xp->query("//m:row[@r='{$r}']/m:c") as $cell) {
        $ref = $cell->getAttribute('r');
        $type = $cell->getAttribute('t');
        $value = '';
        if ($type === 's') {
            $idx = (int) ($xp->query('m:v', $cell)->item(0)?->textContent ?? 0);
            $value = $shared[$idx] ?? '';
        } elseif ($type === 'inlineStr') {
            $value = $xp->query('.//m:t', $cell)->item(0)?->textContent ?? '';
        } else {
            $value = $xp->query('m:v', $cell)->item(0)?->textContent ?? '';
        }
        if (trim((string) $value) !== '') {
            echo $ref . ' = ' . $value . PHP_EOL;
        }
    }
}
