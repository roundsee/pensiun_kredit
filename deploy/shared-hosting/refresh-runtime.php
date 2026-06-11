<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');
$cacheDir = $basePath . '/bootstrap/cache';

echo "=== Refresh Runtime ===\n\n";
echo "basePath: $basePath\n\n";

// 1) Reset OPcache if available
if (function_exists('opcache_reset')) {
    $ok = opcache_reset();
    echo "opcache_reset: " . ($ok ? 'OK' : 'FAILED') . "\n";
} else {
    echo "opcache_reset: not available\n";
}

// 2) Remove bootstrap cache manifests that can hold stale provider state
$targets = [
    $cacheDir . '/packages.php',
    $cacheDir . '/services.php',
    $cacheDir . '/config.php',
    $cacheDir . '/events.php',
    $cacheDir . '/compiled.php',
    $cacheDir . '/routes.php',
    $cacheDir . '/routes-v7.php',
];

echo "\nremove cache files:\n";
foreach ($targets as $file) {
    if (!file_exists($file)) {
        echo "- " . basename($file) . ": not found\n";
        continue;
    }
    $removed = @unlink($file);
    echo "- " . basename($file) . ': ' . ($removed ? 'deleted' : 'FAILED') . "\n";
}

// 3) Bootstrap and verify route table sees __ping
echo "\nbootstrap + route check:\n";
try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    $router = $app->make('router');
    $routes = $router->getRoutes();

    $hasPing = false;
    foreach ($routes as $route) {
        $uri = $route->uri();
        if ($uri === '__ping') {
            $hasPing = true;
            break;
        }
    }

    echo "route_count: " . count($routes) . "\n";
    echo "has___ping: " . ($hasPing ? 'YES' : 'NO') . "\n";

    try {
        $request = Illuminate\Http\Request::create('/__ping', 'GET');
        $match = $routes->match($request);
        echo "match___ping: YES (uri=" . $match->uri() . ")\n";
    } catch (Throwable $e) {
        echo "match___ping: NO\n";
        echo "match_error: " . $e->getMessage() . "\n";
    }
} catch (Throwable $e) {
    echo "bootstrap_error: " . get_class($e) . "\n";
    echo "message: " . $e->getMessage() . "\n";
    echo "file: " . $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\nDone.\n";
