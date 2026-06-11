<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=UTF-8');

use Illuminate\Http\Request;

$basePath = __DIR__ . '/../laravel_newsys';

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    echo "boot: ok\n";

    $request = Request::create('/', 'GET');
    $response = $app->handleRequest($request);

    echo "response: ok\n";
    if (method_exists($response, 'getStatusCode')) {
        echo 'status: ' . $response->getStatusCode() . "\n";
    }
} catch (Throwable $throwable) {
    echo "root_dispatch: failed\n";
    echo get_class($throwable) . "\n";
    echo $throwable->getMessage() . "\n";
    echo $throwable->getFile() . ':' . $throwable->getLine() . "\n";
    echo $throwable->getTraceAsString() . "\n";
}