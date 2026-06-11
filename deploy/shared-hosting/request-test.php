<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Direct Request Handler Test ===\n\n";

$basePath = realpath(__DIR__ . '/../laravel_newsys');

try {
    echo "1. require autoload...\n";
    require $basePath . '/vendor/autoload.php';
    echo "   OK\n";

    echo "2. bootstrap app...\n";
    $app = require_once $basePath . '/bootstrap/app.php';
    echo "   OK\n";

    echo "3. make kernel...\n";
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "   OK\n";

    echo "4. create request for /__ping...\n";
    $request = \Illuminate\Http\Request::create('/__ping', 'GET');
    echo "   OK\n";

    echo "5. kernel->handle()...\n";
    $response = $kernel->handle($request);
    echo "   OK\n";

    echo "6. response status: " . $response->getStatusCode() . "\n";
    echo "7. response body: " . substr($response->getContent(), 0, 100) . "\n";

    echo "8. kernel->terminate()...\n";
    $kernel->terminate($request, $response);
    echo "   OK\n";

    echo "\n=== SUCCESS ===\n";
} catch (Throwable $e) {
    echo "\n=== CAUGHT EXCEPTION ===\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "Trace:\n" . substr($e->getTraceAsString(), 0, 2000) . "\n";
}
