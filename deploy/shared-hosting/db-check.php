<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=UTF-8');

$basePath = realpath(__DIR__ . '/../laravel_newsys');
echo "basePath: $basePath\n\n";

// --- 1. Read .env ---
$envFile = $basePath . '/.env';
echo "--- .env values ---\n";
if (!file_exists($envFile)) {
    echo ".env NOT FOUND at $envFile\n";
    exit;
}

$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
}

$keys = ['APP_ENV','APP_DEBUG','APP_URL','DB_CONNECTION','DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','SESSION_DRIVER','CACHE_STORE','LOG_CHANNEL'];
foreach ($keys as $k) {
    $val = $env[$k] ?? '(not set)';
    // mask password
    echo "$k=" . (str_contains(strtolower($k), 'pass') ? '***' : $val) . "\n";
}

// --- 2. Test DB connection ---
echo "\n--- DB connection test ---\n";
$driver   = $env['DB_CONNECTION'] ?? 'mysql';
$host     = $env['DB_HOST']       ?? '127.0.0.1';
$port     = $env['DB_PORT']       ?? '3306';
$dbname   = $env['DB_DATABASE']   ?? '';
$username = $env['DB_USERNAME']   ?? '';
$password = $env['DB_PASSWORD']   ?? '';

echo "driver: $driver\n";
echo "host:   $host\n";
echo "port:   $port\n";
echo "dbname: $dbname\n";
echo "user:   $username\n";

try {
    $dsn = "$driver:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    echo "connection: OK\n";

    // Check sessions table
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "tables (" . count($tables) . "): " . implode(', ', array_slice($tables, 0, 20)) . "\n";
    if (in_array('sessions', $tables)) {
        echo "sessions table: EXISTS\n";
    } else {
        echo "sessions table: MISSING\n";
    }

} catch (Throwable $e) {
    echo "connection: FAILED\n";
    echo "error: " . $e->getMessage() . "\n";
}

// --- 3. Check storage paths writable ---
echo "\n--- storage writable check ---\n";
$paths = [
    'storage/framework/sessions',
    'storage/framework/cache/data',
    'storage/framework/views',
    'storage/logs',
];
foreach ($paths as $p) {
    $full = $basePath . '/' . $p;
    $exists = is_dir($full) ? 'dir' : (file_exists($full) ? 'file' : 'MISSING');
    $writable = is_writable($full) ? 'writable' : 'NOT writable';
    echo "$p: $exists, $writable\n";
}

// --- 4. Check log channel ---
echo "\n--- log config ---\n";
$logChannel = $env['LOG_CHANNEL'] ?? '(not set)';
echo "LOG_CHANNEL: $logChannel\n";
if ($logChannel === 'stack' || $logChannel === 'daily' || $logChannel === 'single') {
    $logFile = $basePath . '/storage/logs/laravel.log';
    echo "log file exists: " . (file_exists($logFile) ? 'yes' : 'no') . "\n";
    echo "log file writable: " . (is_writable(dirname($logFile)) ? 'yes' : 'no') . "\n";
}
