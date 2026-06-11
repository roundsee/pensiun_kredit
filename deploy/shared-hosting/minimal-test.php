<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Minimal Laravel Test (no routing) ===\n\n";

$basePath = realpath(__DIR__ . '/../laravel_newsys');

try {
    echo "1. require autoload...\n";
    require $basePath . '/vendor/autoload.php';

    echo "2. bootstrap app...\n";
    $app = require_once $basePath . '/bootstrap/app.php';

    echo "3. make kernel...\n";
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

    echo "4. create plain response...\n";
    $response = new \Illuminate\Http\Response('test-ok', 200);

    echo "5. send response...\n";
    $response->send();

    echo "6. after send...\n";

    echo "\n=== SUCCESS ===\n";
} catch (Throwable $e) {
    echo "\n=== EXCEPTION ===\n";
    echo get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}
