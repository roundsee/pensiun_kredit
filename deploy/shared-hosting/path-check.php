<?php
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Hostinger Path Validation ===\n\n";

// Current directory
echo "Current dir: " . __DIR__ . "\n";

// Expected Laravel folder
$laravelPath = __DIR__ . '/../laravel_newsys';
echo "Laravel path expected: $laravelPath\n";
echo "Laravel path exists: " . (is_dir($laravelPath) ? 'YES' : 'NO') . "\n";

// Check key files
$checks = [
    'vendor/autoload.php',
    'bootstrap/app.php',
    '.env',
    'storage/framework/sessions',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/services.php',
];

echo "\nKey files check:\n";
foreach ($checks as $file) {
    $fullPath = $laravelPath . '/' . $file;
    $exists = file_exists($fullPath);
    $type = is_dir($fullPath) ? 'dir' : (file_exists($fullPath) ? 'file' : 'MISSING');
    echo "  $file: " . ($exists ? $type : 'MISSING') . "\n";
}

// Check if we can require autoload
echo "\nAutoload test:\n";
try {
    require $laravelPath . '/vendor/autoload.php';
    echo "  autoload: OK\n";
} catch (Throwable $e) {
    echo "  autoload: FAILED - " . $e->getMessage() . "\n";
}

echo "\nDone\n";
