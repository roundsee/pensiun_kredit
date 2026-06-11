<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');
$routesFile = $basePath . '/routes/web.php';

echo "=== Router Dispatch Test (No Kernel) ===\n\n";

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    Illuminate\Support\Facades\Facade::setFacadeApplication($app);

    $router = $app->make('router');
    echo "routes before include: " . count($router->getRoutes()) . "\n";

    require $routesFile;

    echo "routes after include: " . count($router->getRoutes()) . "\n";

    $request = Illuminate\Http\Request::create('/__ping', 'GET');
    $app->instance('request', $request);

    echo "dispatch start...\n";
    $response = $router->dispatch($request);
    echo "dispatch done\n";

    echo "status: " . $response->getStatusCode() . "\n";
    echo "body: " . $response->getContent() . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . get_class($e) . "\n";
    echo "message: " . $e->getMessage() . "\n";
    echo "file: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "trace: " . substr($e->getTraceAsString(), 0, 2000) . "\n";
}

echo "\nDone.\n";
