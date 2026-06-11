<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');
$traceFile = sys_get_temp_dir() . '/bootstrap_step_trace_' . time() . '.log';

$trace = fopen($traceFile, 'w');
$lastStep = 'init';

$log = function (string $msg) use ($trace, &$lastStep): void {
    $lastStep = $msg;
    fwrite($trace, date('H:i:s') . ' ' . $msg . "\n");
    fflush($trace);
};

register_shutdown_function(function () use ($traceFile, $trace, &$lastStep) {
    $err = error_get_last();
    fwrite($trace, date('H:i:s') . ' shutdown reached' . "\n");
    fflush($trace);
    fclose($trace);

    echo "=== Bootstrap Step Check ===\n\n";
    echo "trace file: $traceFile\n";
    echo "last step: $lastStep\n\n";

    if (file_exists($traceFile)) {
        echo "--- trace ---\n";
        readfile($traceFile);
        echo "\n";
    }

    if ($err) {
        echo "--- shutdown error ---\n";
        echo 'type: ' . $err['type'] . "\n";
        echo 'message: ' . $err['message'] . "\n";
        echo 'file: ' . $err['file'] . ':' . $err['line'] . "\n";
    } else {
        echo "--- shutdown: no fatal captured ---\n";
    }
});

try {
    $log('autoload start');
    require $basePath . '/vendor/autoload.php';
    $log('autoload done');

    $app = require_once $basePath . '/bootstrap/app.php';
    $log('app created');

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $log('kernel made: ' . get_class($kernel));

    $rm = new ReflectionMethod($kernel, 'bootstrappers');
    $rm->setAccessible(true);
    $bootstrappers = $rm->invoke($kernel);

    $log('bootstrappers count: ' . count($bootstrappers));

    foreach ($bootstrappers as $idx => $bootstrapper) {
        $n = $idx + 1;
        $log("bootstrapper[$n] start: $bootstrapper");

        $instance = $app->make($bootstrapper);
        $instance->bootstrap($app);

        $log("bootstrapper[$n] done: $bootstrapper");
    }

    $log('all bootstrappers done');

    echo "Bootstrap completed without crash.\n";
} catch (Throwable $e) {
    $log('exception: ' . get_class($e) . ' - ' . $e->getMessage());

    echo "Caught exception before shutdown dump:\n";
    echo get_class($e) . "\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}
