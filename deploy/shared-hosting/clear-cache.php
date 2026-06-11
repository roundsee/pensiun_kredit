<?php
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');

echo "=== Route Cache Check & Clear ===\n\n";

// Check if route cache exists
$routeCacheFile = $basePath . '/bootstrap/cache/routes-v7.php';
$routeCacheAlt = $basePath . '/bootstrap/cache/routes.php';

echo "1. Check route cache files:\n";
echo "   $routeCacheFile exists: " . (file_exists($routeCacheFile) ? 'YES' : 'no') . "\n";
echo "   $routeCacheAlt exists: " . (file_exists($routeCacheAlt) ? 'YES' : 'no') . "\n";

// Check bootstrap cache dir
$cacheDir = $basePath . '/bootstrap/cache';
$files = glob($cacheDir . '/routes*');
echo "\n2. All route cache files in bootstrap/cache/routes*:\n";
if (!empty($files)) {
    foreach ($files as $f) {
        echo "   " . basename($f) . " (" . filesize($f) . " bytes)\n";
    }
} else {
    echo "   (none found)\n";
}

// Try to delete them
echo "\n3. Attempting to delete route cache files...\n";
foreach ($files as $f) {
    if (unlink($f)) {
        echo "   DELETED: " . basename($f) . "\n";
    } else {
        echo "   FAILED: " . basename($f) . "\n";
    }
}

// Check other cache files
echo "\n4. Other bootstrap cache files:\n";
$otherCache = glob($cacheDir . '/*.php');
foreach ($otherCache as $f) {
    echo "   " . basename($f) . " (" . filesize($f) . " bytes)\n";
}

echo "\nDone. Try accessing /__ping again after clearing.\n";
