<?php

register_shutdown_function(static function (): void {
    $err = error_get_last();
    if ($err === null || !in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }
    if (headers_sent()) {
        return;
    }
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    $msg = $err['message'] . ' (' . basename($err['file']) . ':' . $err['line'] . ')';
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body>';
    echo '<h1>Something went wrong</h1>';
    echo '<p style="color:#800">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p><a href="install.php">install.php</a> · <a href="setup-config.php">setup-config.php</a></p>';
    echo '</body></html>';
});

/**
 * @return array{ok: bool, conn: ?mysqli, error: string, host: string}
 */
function dbConnectAttempt(string $host, string $user, string $pass, string $name): array
{
    $conn = mysqli_init();
    if (!$conn) {
        return ['ok' => false, 'conn' => null, 'error' => 'mysqli_init failed', 'host' => $host];
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    $ok = @mysqli_real_connect($conn, $host, $user, $pass, $name);
    if ($ok) {
        mysqli_set_charset($conn, 'utf8mb4');

        return ['ok' => true, 'conn' => $conn, 'error' => '', 'host' => $host];
    }

    $err = mysqli_connect_error();
    mysqli_close($conn);

    return ['ok' => false, 'conn' => null, 'error' => $err, 'host' => $host];
}

function dbConnect(array $config): ?mysqli
{
    if (!function_exists('mysqli_init')) {
        return null;
    }

    $user = (string) ($config['db_user'] ?? '');
    $pass = (string) ($config['db_pass'] ?? '');
    $name = (string) ($config['db_name'] ?? '');
    $primaryHost = trim((string) ($config['db_host'] ?? 'localhost'));

    $hosts = [$primaryHost];
    if (strtolower($primaryHost) !== 'localhost') {
        $hosts[] = 'localhost';
    }

    $lastError = '';
    foreach ($hosts as $host) {
        if ($host === '') {
            continue;
        }
        $attempt = dbConnectAttempt($host, $user, $pass, $name);
        if ($attempt['ok']) {
            return $attempt['conn'];
        }
        $lastError = $attempt['error'];
        if (stripos($lastError, 'access denied') !== false) {
            break;
        }
    }

    return null;
}

function dbConnectLastError(): string
{
    return mysqli_connect_error();
}
