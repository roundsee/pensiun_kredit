<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

ob_start();

register_shutdown_function(function () {
    $error = error_get_last();
    $output = ob_get_clean();
    if ($output) {
        echo $output;
    }
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR])) {
        echo "\n--- FATAL ERROR ---\n";
        echo "type: " . $error['type'] . "\n";
        echo "message: " . $error['message'] . "\n";
        echo "file: " . $error['file'] . ":" . $error['line'] . "\n";
    } else {
        echo "\n--- shutdown: no fatal error ---\n";
    }
});

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel as HttpKernel;

$basePath = __DIR__ . '/../laravel_newsys';

try {
    require $basePath . '/vendor/autoload.php';
    $app = require_once $basePath . '/bootstrap/app.php';

    echo "boot: ok\n";

    $request = Request::create('/login', 'GET');
    $kernel = $app->make(HttpKernel::class);

    echo "before_handle\n";
    $response = $kernel->handle($request);
    echo "after_handle\n";

    echo 'status: ' . $response->getStatusCode() . "\n";

    $content = $response->getContent();
    if (is_string($content)) {
        echo "body_len: " . strlen($content) . "\n";
        echo "body_preview:\n";
        echo substr($content, 0, 800) . "\n";
    }

    $kernel->terminate($request, $response);
    echo "terminate: ok\n";
} catch (Throwable $throwable) {
    echo "caught: failed\n";
    echo get_class($throwable) . "\n";
    echo $throwable->getMessage() . "\n";
    echo $throwable->getFile() . ':' . $throwable->getLine() . "\n";
    echo substr($throwable->getTraceAsString(), 0, 1500) . "\n";
}
