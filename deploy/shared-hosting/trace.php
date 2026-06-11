<?php
// Write to file to bypass all output buffering issues
$logFile = sys_get_temp_dir() . '/laravel_trace_' . time() . '.txt';
$out = fopen($logFile, 'w');

function trace(string $msg) use ($out): void {
    $line = date('H:i:s') . " " . $msg . "\n";
    fwrite($out, $line);
    fflush($out);
}

register_shutdown_function(function () use ($logFile, $out) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        fwrite($out, "FATAL: type={$error['type']} msg={$error['message']} file={$error['file']}:{$error['line']}\n");
    } else {
        fwrite($out, "shutdown: clean\n");
    }
    fclose($out);

    // Now output the file
    header('Content-Type: text/plain; charset=UTF-8');
    echo "=== TRACE LOG ($logFile) ===\n";
    readfile($logFile);
});

trace("start");

$basePath = realpath(__DIR__ . '/../laravel_newsys');
trace("basePath: $basePath");

require $basePath . '/vendor/autoload.php';
trace("autoload: ok");

$app = require_once $basePath . '/bootstrap/app.php';
trace("bootstrap: ok");

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel as HttpKernel;

$kernel = $app->make(HttpKernel::class);
trace("kernel make: ok");
trace("kernel class: " . get_class($kernel));

// Patch: observe via event system
try {
    $dispatcher = $app->make('events');
    $dispatcher->listen('*', function ($event) use ($out) {
        if (is_string($event)) {
            fwrite($out, "  EVENT: $event\n");
            fflush($out);
        }
    });
    trace("event listener: attached");
} catch (Throwable $e) {
    trace("event listener: failed - " . $e->getMessage());
}

trace("=== calling kernel->handle /up ===");
try {
    $req = Request::create('/up', 'GET');
    trace("request created");

    // Manually run through global middleware to find which one exits
    $response = $kernel->handle($req);

    trace("handle: DONE");
    trace("status: " . $response->getStatusCode());
    trace("body: " . substr($response->getContent(), 0, 300));
    $kernel->terminate($req, $response);
    trace("terminate: ok");
} catch (Throwable $e) {
    trace("EXCEPTION: " . get_class($e) . ": " . $e->getMessage());
    trace("at: " . $e->getFile() . ':' . $e->getLine());
    trace("trace: " . substr($e->getTraceAsString(), 0, 1500));
}

trace("script end");
