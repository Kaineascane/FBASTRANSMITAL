<?php
header('Content-Type: text/plain; charset=UTF-8');
echo "PHP OK " . PHP_VERSION . "\n";

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    echo "config.php: MISSING\n";
    exit;
}
echo "config.php: OK\n";

$config = require $configPath;
$host = $config['db_host'] ?? '';
echo "db_host: {$host}\n";

if (!function_exists('mysqli_connect')) {
    echo "mysqli: MISSING\n";
    exit;
}

$conn = @mysqli_connect(
    (string) ($config['db_host'] ?? ''),
    (string) ($config['db_user'] ?? ''),
    (string) ($config['db_pass'] ?? ''),
    (string) ($config['db_name'] ?? '')
);
if ($conn) {
    echo "database: CONNECTED\n";
    mysqli_close($conn);
} else {
    echo "database: FAILED — " . mysqli_connect_error() . "\n";
}
