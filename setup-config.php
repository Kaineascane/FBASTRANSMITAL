<?php
/**
 * Set MySQL password in config.php via File Manager (avoids install form issues).
 * DELETE this file after the site works.
 */
header('Content-Type: text/html; charset=UTF-8');

$configPath = __DIR__ . '/config.php';
$example = <<<'PHP'
<?php
return [
    'db_host' => 'sql308.infinityfree.com',
    'db_user' => 'if0_42101552_fbas',
    'db_pass' => 'PUT_YOUR_MYSQL_PASSWORD_HERE',
    'db_name' => 'if0_42101552_fbas',
    'debug' => true,
    'app_url' => '',
    'force_https' => false,
    'allow_infinityfree_fallback' => true,
];
PHP;

if (!is_file($configPath)) {
  file_put_contents($configPath, $example);
}

$msg = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = trim((string) ($_POST['db_pass'] ?? ''));
    if ($pass === '' || $pass === 'PUT_YOUR_MYSQL_PASSWORD_HERE') {
        $msg = 'Enter the MySQL password from InfinityFree (after reset).';
    } else {
        $config = is_file($configPath) ? require $configPath : [];
        if (!is_array($config)) {
            $config = [];
        }
        $host = trim((string) ($_POST['db_host'] ?? $config['db_host'] ?? 'sql308.infinityfree.com'));
        $user = trim((string) ($_POST['db_user'] ?? $config['db_user'] ?? 'if0_42101552_fbas'));
        $name = trim((string) ($_POST['db_name'] ?? $config['db_name'] ?? 'if0_42101552_fbas'));

        $configPhp = "<?php\nreturn [\n"
            . "    'db_host' => " . var_export($host, true) . ",\n"
            . "    'db_user' => " . var_export($user, true) . ",\n"
            . "    'db_pass' => " . var_export($pass, true) . ",\n"
            . "    'db_name' => " . var_export($name, true) . ",\n"
            . "    'debug' => true,\n"
            . "    'app_url' => '',\n"
            . "    'force_https' => false,\n"
            . "    'allow_infinityfree_fallback' => true,\n"
            . "];\n";

        file_put_contents($configPath, $configPhp);

        require_once __DIR__ . '/includes/bootstrap.php';
        $conn = dbConnect([
            'db_host' => $host,
            'db_user' => $user,
            'db_pass' => $pass,
            'db_name' => $name,
        ]);

        if ($conn) {
            mysqli_close($conn);
            $ok = true;
            $msg = 'Success! Database connected. Open the app: <a href="index.php">index.php</a>';
        } else {
            $detail = dbConnectLastError();
            $msg = 'Still cannot connect: ' . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8')
                . '<br>Password length sent: ' . strlen($pass) . ' characters. '
                . 'Reset MySQL password in InfinityFree panel, then try again.';
        }
    }
}

function sc_h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Setup config — S.I Transmittal</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 36rem; margin: 2rem auto; padding: 0 1rem; line-height: 1.5; }
    label { display: block; margin-top: 1rem; font-weight: 600; }
    input { width: 100%; padding: 0.5rem; margin-top: 0.25rem; box-sizing: border-box; }
    button { margin-top: 1rem; padding: 0.6rem 1.2rem; background: #0f2744; color: #fff; border: 0; cursor: pointer; }
    .box { background: #fff8e6; border: 1px solid #e6c200; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .ok { background: #e8f5e9; border-color: #4caf50; padding: 1rem; }
    .err { background: #fde8e8; border-color: #e57373; padding: 1rem; }
  </style>
</head>
<body>
  <h1>MySQL password setup</h1>

  <div class="box">
    <strong>Why install.php fails even when phpMyAdmin works:</strong>
    phpMyAdmin at <code>php-myadmin.net</code> often logs you in <em>automatically</em>
    from InfinityFree — you never type the real MySQL password. The website must use the
    actual MySQL password from the control panel.
  </div>

  <p><strong>Do this once:</strong></p>
  <ol>
    <li>InfinityFree → <strong>MySQL Databases</strong> → <code>if0_42101552_fbas</code> → <strong>Change Password</strong></li>
    <li>Set a simple new password (letters and numbers only), e.g. <code>FbasTransmittal2026</code></li>
    <li>Type that <strong>new</strong> password below (not your InfinityFree login password)</li>
  </ol>

  <?php if ($msg !== ''): ?>
  <div class="<?= $ok ? 'ok' : 'err' ?>"><?= $ok ? $msg : $msg ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <label>MySQL hostname</label>
    <input name="db_host" value="<?= sc_h($_POST['db_host'] ?? 'sql308.infinityfree.com') ?>" required>

    <label>MySQL username</label>
    <input name="db_user" value="<?= sc_h($_POST['db_user'] ?? 'if0_42101552_fbas') ?>" required>

    <label>MySQL database name</label>
    <input name="db_name" value="<?= sc_h($_POST['db_name'] ?? 'if0_42101552_fbas') ?>" required>

    <label>New MySQL password (from step 2 above)</label>
    <input name="db_pass" type="password" required autocomplete="new-password">

    <button type="submit">Save &amp; test connection</button>
  </form>

  <p style="margin-top:2rem;font-size:0.9rem;">
    Or edit <code>config.php</code> in File Manager and set <code>db_pass</code> manually, then open
    <a href="health.php">health.php</a>.
  </p>
</body>
</html>
