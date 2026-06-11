<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');
$routesFile = $basePath . '/routes/web.php';

echo "=== Routes Include Debug ===\n\n";
echo "basePath: $basePath\n";
echo "routes file: $routesFile\n\n";

if (!file_exists($routesFile)) {
    echo "routes/web.php not found\n";
    exit;
}

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    // Set facade application explicitly so Route facade can be used during manual include.
    Illuminate\Support\Facades\Facade::setFacadeApplication($app);

    $router = $app->make('router');
    echo "routes before include: " . count($router->getRoutes()) . "\n";

    require $routesFile;

    $count = count($router->getRoutes());
    echo "routes after include: $count\n";

    $uris = [];
    foreach ($router->getRoutes() as $route) {
        $uris[] = $route->uri();
    }

    echo "has __ping: " . (in_array('__ping', $uris, true) ? 'YES' : 'NO') . "\n";
    echo "has login: " . (in_array('login', $uris, true) ? 'YES' : 'NO') . "\n";
    echo "first 20 uris: " . implode(', ', array_slice($uris, 0, 20)) . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . get_class($e) . "\n";
    echo "message: " . $e->getMessage() . "\n";
    echo "file: " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "trace: " . substr($e->getTraceAsString(), 0, 2000) . "\n";
}

echo "\nDone.\n";
