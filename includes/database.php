<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config-loader.php';

$config = loadAppConfig();
if ($config === []) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>Setup required</h1>';
    echo '<p><a href="install.php">Open install.php</a> to connect your InfinityFree MySQL database.</p>';
    exit;
}

$host = (string) ($config['db_host'] ?? 'localhost');
$user = (string) ($config['db_user'] ?? '');
$name = (string) ($config['db_name'] ?? '');
$debug = (bool) ($config['debug'] ?? false);

if (!function_exists('mysqli_init')) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<h1>PHP mysqli extension missing</h1>';
    exit;
}

$conn = dbConnect($config);

if (!$conn) {
    header('Content-Type: text/html; charset=UTF-8');
    http_response_code(503);
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database setup</title></head><body>';
    echo '<h1>Database connection failed</h1>';
    echo '<p>Your <code>config.php</code> MySQL settings are wrong or the database was not created yet.</p>';
    echo '<p><strong><a href="install.php">Run install.php</a></strong> and paste all four values from InfinityFree → MySQL Databases.</p>';
    echo '<p>Username must be the <em>full</em> MySQL user (e.g. <code>if0_42101552_xxxxx</code>), not <code>if0_42101552</code> alone.</p>';
    if ($debug) {
        @mysqli_report(MYSQLI_REPORT_OFF);
        $probe = mysqli_init();
        if ($probe) {
            mysqli_options($probe, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
            $pass = (string) ($config['db_pass'] ?? '');
            @mysqli_real_connect($probe, $host, $user, $pass, $name);
            echo '<p><strong>Error:</strong> ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8') . '</p>';
            mysqli_close($probe);
        }
    }
    echo '</body></html>';
    exit;
}
