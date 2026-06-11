<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

use Illuminate\Contracts\Http\Kernel as HttpKernel;

$basePath = realpath(__DIR__ . '/../laravel_newsys');

// --- 1. PHP config that could cause silent exit ---
echo "--- PHP config ---\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "open_basedir: " . (ini_get('open_basedir') ?: '(none)') . "\n";
echo "output_buffering: " . ini_get('output_buffering') . "\n";
echo "sys_get_temp_dir: " . sys_get_temp_dir() . "\n";
echo "memory_now: " . round(memory_get_usage(true) / 1024 / 1024, 2) . "MB\n";

// --- 2. Required extensions ---
echo "\n--- extensions ---\n";
$needed = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl'];
foreach ($needed as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'yes' : 'MISSING') . "\n";
}

// --- 3. APP_KEY check ---
echo "\n--- APP_KEY ---\n";
$env = [];
foreach (file($basePath . '/.env') as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, " \t\n\r\"'");
}
$key = $env['APP_KEY'] ?? '(not set)';
echo "APP_KEY set: " . ($key !== '(not set)' && $key !== '' ? 'yes (' . strlen($key) . ' chars)' : 'NO - MISSING!') . "\n";
echo "APP_KEY prefix: " . substr($key, 0, 10) . "...\n";

// --- 4. Blade compilation test ---
echo "\n--- Blade compile test ---\n";
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
echo "bootstrap: ok\n";

try {
    $blade = $app->make('view');
    $viewPath = $basePath . '/resources/views';
    // Find first blade file
    $views = glob($viewPath . '/*.blade.php');
    if (empty($views)) {
        $views = glob($viewPath . '/**/*.blade.php');
    }
    if (!empty($views)) {
        $firstView = $views[0];
        echo "test view: $firstView\n";
        // Try to compile it
        $compiler = $app->make('blade.compiler');
        $compiled = $compiler->getCompiledPath($firstView);
        echo "compiled path: $compiled\n";
        echo "compiled exists: " . (file_exists($compiled) ? 'yes' : 'no') . "\n";
        $compiler->compile($firstView);
        echo "compile: ok\n";
        echo "compiled size: " . filesize($compiled) . " bytes\n";
    } else {
        echo "no blade views found\n";
    }
} catch (Throwable $e) {
    echo "BLADE ERROR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "at: " . $e->getFile() . ':' . $e->getLine() . "\n";
}

// --- 5. Kernel middleware list ---
echo "\n--- kernel middleware ---\n";
try {
    $kernel = $app->make(HttpKernel::class);
    // Access protected middleware property via reflection
    $ref = new ReflectionClass($kernel);
    foreach (['middleware', 'middlewareGroups'] as $prop) {
        if ($ref->hasProperty($prop)) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $val = $p->getValue($kernel);
            echo "$prop: " . json_encode(array_values((array)$val), JSON_UNESCAPED_SLASHES) . "\n";
        }
    }
} catch (Throwable $e) {
    echo "middleware list error: " . $e->getMessage() . "\n";
}

echo "\ndone\n";
