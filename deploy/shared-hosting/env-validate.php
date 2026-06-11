<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');
$envPath = $basePath . '/.env';

echo "=== .env Validation ===\n\n";
echo "env path: $envPath\n";
echo "exists: " . (file_exists($envPath) ? 'YES' : 'NO') . "\n";
if (!file_exists($envPath)) {
    exit;
}

echo "size: " . filesize($envPath) . " bytes\n\n";

require $basePath . '/vendor/autoload.php';

try {
    // Parse only; do not bootstrap Laravel.
    $dotenv = Dotenv\Dotenv::createImmutable($basePath);
    $data = $dotenv->load();

    echo "RESULT: VALID\n";
    echo "loaded keys: " . count($data) . "\n";

    $important = ['APP_ENV', 'APP_DEBUG', 'APP_URL', 'APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    echo "\nimportant keys:\n";
    foreach ($important as $k) {
        $v = $_ENV[$k] ?? '(not set)';
        if ($k === 'APP_KEY' && $v !== '(not set)') {
            $v = substr($v, 0, 12) . '...';
        }
        echo "- $k=$v\n";
    }
} catch (Throwable $e) {
    echo "RESULT: INVALID\n";
    echo "class: " . get_class($e) . "\n";
    echo "message: " . $e->getMessage() . "\n";
    echo "file: " . $e->getFile() . ':' . $e->getLine() . "\n";

    echo "\n--- First 120 lines of .env (numbered) ---\n";
    $lines = file($envPath);
    $max = min(120, count($lines));
    for ($i = 0; $i < $max; $i++) {
        $lineNo = $i + 1;
        $line = rtrim($lines[$i], "\r\n");

        // mask secrets
        if (stripos($line, 'APP_KEY=') === 0) {
            $line = 'APP_KEY=***';
        }
        if (stripos($line, 'DB_PASSWORD=') === 0) {
            $line = 'DB_PASSWORD=***';
        }
        if (stripos($line, 'MAIL_PASSWORD=') === 0) {
            $line = 'MAIL_PASSWORD=***';
        }

        echo str_pad((string)$lineNo, 3, ' ', STR_PAD_LEFT) . ': ' . $line . "\n";
    }
}

echo "\nDone.\n";
