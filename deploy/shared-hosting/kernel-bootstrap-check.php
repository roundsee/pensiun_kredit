<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');

echo "=== Kernel Bootstrap Check ===\n\n";
echo "basePath: $basePath\n\n";

register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) {
        echo "\n[SHUTDOWN ERROR]\n";
        echo "type: {$e['type']}\n";
        echo "message: {$e['message']}\n";
        echo "file: {$e['file']}:{$e['line']}\n";
    }
});

try {
    require $basePath . '/vendor/autoload.php';
    echo "autoload: OK\n";

    $app = require_once $basePath . '/bootstrap/app.php';
    echo "app create: OK\n";
    echo "bound(config) before kernel: " . ($app->bound('config') ? 'YES' : 'NO') . "\n";

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "make kernel: OK (" . get_class($kernel) . ")\n";
    echo "bound(config) before bootstrap: " . ($app->bound('config') ? 'YES' : 'NO') . "\n";

    // Try explicit kernel bootstrap (this should register config repository).
    echo "calling kernel bootstrap...\n";
    if (method_exists($kernel, 'bootstrap')) {
        $rm = new ReflectionMethod($kernel, 'bootstrap');
        $rm->setAccessible(true);
        $rm->invoke($kernel);
        echo "kernel bootstrap: OK\n";
    } else {
        echo "kernel bootstrap method: NOT FOUND\n";
    }

    echo "bound(config) after bootstrap: " . ($app->bound('config') ? 'YES' : 'NO') . "\n";

    if ($app->bound('config')) {
        try {
            $config = $app->make('config');
            echo "make(config): OK (" . get_class($config) . ")\n";
            echo "app.env: " . $config->get('app.env', '(null)') . "\n";
            echo "app.debug: " . var_export($config->get('app.debug', null), true) . "\n";
            echo "app.url: " . $config->get('app.url', '(null)') . "\n";
            echo "logging.default: " . $config->get('logging.default', '(null)') . "\n";
        } catch (Throwable $te) {
            echo "make(config): FAILED\n";
            echo get_class($te) . ": " . $te->getMessage() . "\n";
            echo $te->getFile() . ':' . $te->getLine() . "\n";
        }
    }

    echo "\n[Provider check]\n";
    echo "bound(events): " . ($app->bound('events') ? 'YES' : 'NO') . "\n";
    echo "bound(log): " . ($app->bound('log') ? 'YES' : 'NO') . "\n";
    echo "bound(router): " . ($app->bound('router') ? 'YES' : 'NO') . "\n";
    echo "bound(view): " . ($app->bound('view') ? 'YES' : 'NO') . "\n";
} catch (Throwable $e) {
    echo "\n[EXCEPTION]\n";
    echo get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo substr($e->getTraceAsString(), 0, 2500) . "\n";
}

echo "\nDone.\n";
