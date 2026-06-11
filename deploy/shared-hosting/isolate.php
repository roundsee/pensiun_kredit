<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

ob_start();
register_shutdown_function(function () {
    $error = error_get_last();
    $out = ob_get_clean();
    if ($out) echo $out;
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        echo "\n--- FATAL: type={$error['type']} msg={$error['message']} file={$error['file']}:{$error['line']} ---\n";
    } else {
        echo "\n--- shutdown: clean ---\n";
    }
});

$basePath = realpath(__DIR__ . '/../laravel_newsys');

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
echo "boot: ok\n";

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel as HttpKernel;

$kernel = $app->make(HttpKernel::class);
echo "kernel: ok\n";

// --- Test 1: /up (health check, minimal middleware) ---
echo "\n=== TEST /up ===\n";
try {
    $req = Request::create('/up', 'GET');
    echo "handle /up: start\n";
    $res = $kernel->handle($req);
    echo "handle /up: done\n";
    echo "status: " . $res->getStatusCode() . "\n";
    echo "body: " . substr($res->getContent(), 0, 200) . "\n";
    $kernel->terminate($req, $res);
} catch (Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo substr($e->getTraceAsString(), 0, 1000) . "\n";
}

// --- Test 2: /login ---
echo "\n=== TEST /login ===\n";
try {
    $kernel2 = $app->make(HttpKernel::class);
    $req2 = Request::create('/login', 'GET');
    echo "handle /login: start\n";
    $res2 = $kernel2->handle($req2);
    echo "handle /login: done\n";
    echo "status: " . $res2->getStatusCode() . "\n";
    echo "body_len: " . strlen($res2->getContent()) . "\n";
    echo "body_preview: " . substr($res2->getContent(), 0, 500) . "\n";
    $kernel2->terminate($req2, $res2);
} catch (Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo substr($e->getTraceAsString(), 0, 1000) . "\n";
}

echo "\ndone\n";
