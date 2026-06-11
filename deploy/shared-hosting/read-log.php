<?php
header('Content-Type: text/plain; charset=UTF-8');

$logFile = realpath(__DIR__ . '/../laravel_newsys') . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "log file not found: $logFile\n";
    exit;
}

$size = filesize($logFile);
echo "log file size: $size bytes\n\n";

if ($size === 0) {
    echo "(log file is empty)\n";
    exit;
}

// Read last 8000 bytes (tail)
$handle = fopen($logFile, 'r');
$tail = '';
if ($size > 8000) {
    fseek($handle, -8000, SEEK_END);
    $tail = fread($handle, 8000);
    echo "--- LAST 8000 bytes of laravel.log ---\n";
} else {
    $tail = fread($handle, $size);
    echo "--- FULL laravel.log ---\n";
}
fclose($handle);

echo $tail;
