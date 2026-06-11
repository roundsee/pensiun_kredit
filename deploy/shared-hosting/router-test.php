<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Test Router Directly (bypass kernel middleware) ===\n\n";

$basePath = realpath(__DIR__ . '/../laravel_newsys');

try {
    echo "1. bootstrap app...\n";
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    echo "2. create request...\n";
    $request = \Illuminate\Http\Request::create('/__ping', 'GET');
    $app->instance('request', $request);

    echo "3. get router...\n";
    $router = $app->make('router');

    echo "4. match route...\n";
    $route = $router->getRoutes()->match($request);
    echo "   route found: " . $route->getName() . "\n";

    echo "5. get controller/closure...\n";
    $controller = $route->getController();
    echo "   controller: " . (is_object($controller) ? get_class($controller) : 'callable') . "\n";

    echo "6. dispatch route...\n";
    $response = $router->dispatch($request);
    echo "   OK\n";

    echo "7. response status: " . $response->getStatusCode() . "\n";
    echo "8. response body: " . $response->getContent() . "\n";

    echo "\n=== SUCCESS ===\n";
} catch (Throwable $e) {
    echo "\n=== EXCEPTION ===\n";
    echo "Class: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "Trace:\n" . substr($e->getTraceAsString(), 0, 1000) . "\n";
}
