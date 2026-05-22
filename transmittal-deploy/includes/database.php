<?php

$configPath = dirname(__DIR__) . '/config.php';
if (!is_file($configPath)) {
    die(
        'Missing config.php. Copy config.example.php to config.php and enter your database settings. '
        . '<a href="HOSTING.md">Hosting guide</a>'
    );
}

$config = require $configPath;

$host = $config['db_host'] ?? 'localhost';
$user = $config['db_user'] ?? '';
$pass = $config['db_pass'] ?? '';
$name = $config['db_name'] ?? '';
$debug = (bool) ($config['debug'] ?? false);

$conn = mysqli_connect($host, $user, $pass, $name);

if (!$conn) {
    $msg = 'Database connection failed. Check config.php (host, user, password, database name).';
    if ($debug) {
        $msg .= ' Error: ' . mysqli_connect_error();
    }
    die($msg);
}

mysqli_set_charset($conn, 'utf8mb4');
