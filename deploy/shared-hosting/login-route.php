<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=UTF-8');

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel as HttpKernel;

$basePath = __DIR__ . '/../laravel_newsys';

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    echo "boot: ok\n";

    $request = Request::create('/login', 'GET');
    $kernel = $app->make(HttpKernel::class);
    $response = $kernel->handle($request);

    echo "handle: ok\n";
    echo 'status: ' . $response->getStatusCode() . "\n";
    echo 'location: ' . ($response->headers->get('Location') ?? '-') . "\n";
    echo 'content_type: ' . ($response->headers->get('Content-Type') ?? '-') . "\n";

    $content = $response->getContent();
    if (is_string($content)) {
        echo "body_preview:\n";
        echo substr($content, 0, 1200) . "\n";
    }

    $kernel->terminate($request, $response);
} catch (Throwable $throwable) {
    echo "login_dispatch: failed\n";
    echo get_class($throwable) . "\n";
    echo $throwable->getMessage() . "\n";
    echo $throwable->getFile() . ':' . $throwable->getLine() . "\n";
    echo $throwable->getTraceAsString() . "\n";
}