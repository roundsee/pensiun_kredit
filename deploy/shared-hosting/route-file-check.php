<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');
$bootstrapApp = $basePath . '/bootstrap/app.php';
$webRoutes = $basePath . '/routes/web.php';

echo "=== Route File Check ===\n\n";
echo "basePath: $basePath\n\n";

function infoFile(string $path, string $label): void
{
    echo "[$label]\n";
    echo "path: $path\n";
    echo "exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
    if (!file_exists($path)) {
        echo "\n";
        return;
    }
    echo "readable: " . (is_readable($path) ? 'YES' : 'NO') . "\n";
    echo "size: " . filesize($path) . " bytes\n";
    echo "mtime: " . date('Y-m-d H:i:s', filemtime($path)) . "\n";
    echo "\n";
}

infoFile($bootstrapApp, 'bootstrap/app.php');
infoFile($webRoutes, 'routes/web.php');

if (file_exists($bootstrapApp)) {
    $appContent = file_get_contents($bootstrapApp);
    echo "[bootstrap/app.php contains]\n";
    echo "withRouting: " . (strpos($appContent, 'withRouting(') !== false ? 'YES' : 'NO') . "\n";
    echo "routes/web.php ref: " . (strpos($appContent, 'routes/web.php') !== false ? 'YES' : 'NO') . "\n";
    echo "health /up ref: " . (strpos($appContent, "health: '/up'") !== false ? 'YES' : 'NO') . "\n\n";
}

if (file_exists($webRoutes)) {
    $webContent = file_get_contents($webRoutes);
    echo "[routes/web.php contains]\n";
    echo "__ping route: " . (strpos($webContent, "Route::get('/__ping'") !== false ? 'YES' : 'NO') . "\n";
    echo "login route: " . (strpos($webContent, "Route::get('/login'") !== false ? 'YES' : 'NO') . "\n";

    echo "\n[first 60 lines of routes/web.php]\n";
    $lines = explode("\n", $webContent);
    $max = min(60, count($lines));
    for ($i = 0; $i < $max; $i++) {
        $num = $i + 1;
        echo str_pad((string)$num, 3, ' ', STR_PAD_LEFT) . ': ' . $lines[$i] . "\n";
    }
    echo "\n";
}

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $bootstrapApp;

    echo "[manual include routes/web.php]\n";
    $router = $app->make('router');
    $before = count($router->getRoutes());
    echo "routes before include: $before\n";

    // Include the route file manually to see if it can register routes.
    // This runs in the same process and will reveal parse/runtime issues.
    require $webRoutes;

    $after = count($router->getRoutes());
    echo "routes after include: $after\n";

    $hasPing = false;
    foreach ($router->getRoutes() as $r) {
        if ($r->uri() === '__ping') {
            $hasPing = true;
            break;
        }
    }
    echo "has __ping after include: " . ($hasPing ? 'YES' : 'NO') . "\n";
} catch (Throwable $e) {
    echo "\n[ERROR while including routes]\n";
    echo get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}

echo "\nDone.\n";
