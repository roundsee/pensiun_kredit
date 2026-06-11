<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Test with disabled middleware ===\n\n";

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

    // Disable all middleware except SubstituteBindings
    echo "4. disable middleware...\n";
    $kernel->prependMiddlewareToGroup('web', function($request, $next) {
        echo "   [middleware: bypass-all]\n";
        return $next($request);
    });
    echo "   OK\n";

    echo "5. create request for /__ping...\n";
    $request = \Illuminate\Http\Request::create('/__ping', 'GET');
    echo "   OK\n";

    echo "6. kernel->handle()...\n";
    $response = $kernel->handle($request);
    echo "   OK\n";

    echo "7. response status: " . $response->getStatusCode() . "\n";
    echo "8. response body: " . substr($response->getContent(), 0, 100) . "\n";

    echo "\n=== SUCCESS ===\n";
} catch (Throwable $e) {
    echo "\n=== CAUGHT EXCEPTION ===\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "Trace:\n" . substr($e->getTraceAsString(), 0, 2000) . "\n";
}
