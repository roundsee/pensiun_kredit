<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=UTF-8');

$basePath = __DIR__ . '/../laravel_newsys';

echo "base_path: {$basePath}\n";
echo 'autoload: ' . (file_exists($basePath . '/vendor/autoload.php') ? "ok\n" : "missing\n");
echo 'bootstrap: ' . (file_exists($basePath . '/bootstrap/app.php') ? "ok\n" : "missing\n");
echo '.env: ' . (file_exists($basePath . '/.env') ? "ok\n" : "missing\n");

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    echo "framework_bootstrap: ok\n";

    if (method_exists($app, 'version')) {
        echo 'laravel_version: ' . $app->version() . "\n";
    }
} catch (Throwable $throwable) {
    echo "framework_bootstrap: failed\n";
    echo get_class($throwable) . "\n";
    echo $throwable->getMessage() . "\n";
    echo $throwable->getFile() . ':' . $throwable->getLine() . "\n";
}