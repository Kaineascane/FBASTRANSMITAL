<?php
/**
 * One-time MySQL setup for InfinityFree. Delete this file after success.
 */
$root = __DIR__;
$configPath = $root . '/config.php';
$lockPath = $root . '/.install-complete';
$sqlPath = $root . '/sql/setup-hosting.sql';

if (is_file($lockPath) && !isset($_GET['force'])) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Already installed</title></head><body>';
    echo '<h1>Already installed</h1><p><a href="index.php">Open app</a></p>';
    echo '<p>Delete <code>install.php</code> and <code>.install-complete</code> from the server for security.</p>';
    echo '</body></html>';
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim((string) ($_POST['db_host'] ?? ''));
    $user = trim((string) ($_POST['db_user'] ?? ''));
    $pass = trim((string) ($_POST['db_pass'] ?? ''));
    $passLen = strlen($pass);
    $name = trim((string) ($_POST['db_name'] ?? ''));

    if ($host === '' || $user === '' || $name === '') {
        $error = 'Hostname, username, and database name are required.';
    } elseif ($pass === '') {
        $error = 'MySQL password is required. Use the password shown when you created the database in InfinityFree (not your website login unless you chose the same).';
    } elseif (preg_match('/_xxx$/i', $name) || stripos($name, 'something') !== false) {
        $error = 'Database name looks like a placeholder. Copy the exact database name from InfinityFree → MySQL Databases (e.g. if0_42101552_fbas).';
    } elseif ($user === 'if0_42101552' || strpos($user, '_') === false) {
        $error = 'MySQL username must be the full name from the panel (e.g. if0_42101552_fbas), not just if0_42101552. Open InfinityFree → MySQL Databases and copy the Username column exactly.';
    } elseif (!function_exists('mysqli_connect')) {
        $error = 'mysqli extension is not enabled on this server.';
    } else {
        require_once __DIR__ . '/includes/bootstrap.php';
        $conn = dbConnect([
            'db_host' => $host,
            'db_user' => $user,
            'db_pass' => $pass,
            'db_name' => $name,
        ]);
        if (!$conn) {
            $error = 'Connection failed. Check hostname (sql###.infinityfree.com), full username, password, and database name.';
            @mysqli_report(MYSQLI_REPORT_OFF);
            $probe = mysqli_init();
            if ($probe) {
                mysqli_options($probe, MYSQLI_OPT_CONNECT_TIMEOUT, 8);
                @mysqli_real_connect($probe, $host, $user, $pass, $name);
                $detail = mysqli_connect_error();
                if ($detail !== '') {
                    $error .= ' (' . $detail . ')';
                }
                mysqli_close($probe);
            }
            if (stripos($error, 'access denied') !== false) {
                $error .= ' — The MySQL password does not match. phpMyAdmin may have logged you in without your password.'
                    . ' Reset the password in InfinityFree → MySQL Databases → Change Password, then use'
                    . ' <a href="setup-config.php">setup-config.php</a> with the NEW password only.'
                    . ' (Password length you sent: ' . $passLen . ' characters.)';
            }
        } else {
            $sql = file_get_contents($sqlPath);
            if ($sql === false) {
                $error = 'Could not read sql/setup-hosting.sql';
            } else {
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $statement) {
                    if ($statement === '' || stripos($statement, 'CREATE') === false) {
                        continue;
                    }
                    if (!@mysqli_query($conn, $statement)) {
                        $error = 'Could not create tables: ' . mysqli_error($conn);
                        break;
                    }
                }
            }

            if ($error === '') {
                $configPhp = "<?php\nreturn [\n"
                    . "    'db_host' => " . var_export($host, true) . ",\n"
                    . "    'db_user' => " . var_export($user, true) . ",\n"
                    . "    'db_pass' => " . var_export($pass, true) . ",\n"
                    . "    'db_name' => " . var_export($name, true) . ",\n"
                    . "    'debug' => false,\n"
                    . "    'app_url' => 'https://fbastransmittal.infinityfree.io',\n"
                    . "    'force_https' => false,\n"
                    . "    'allow_infinityfree_fallback' => true,\n"
                    . "];\n";

                if (file_put_contents($configPath, $configPhp) === false) {
                    $error = 'Connected, but could not write config.php. Paste settings manually in File Manager.';
                } else {
                    file_put_contents($lockPath, date('c'));
                    header('Location: index.php');
                    exit;
                }
            }
            mysqli_close($conn);
        }
    }
}

if (!function_exists('install_h')) {
    function install_h($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Install — S.I Transmittal</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 32rem; margin: 2rem auto; padding: 0 1rem; }
    label { display: block; margin-top: 1rem; font-weight: 600; }
    input { width: 100%; padding: 0.5rem; margin-top: 0.25rem; box-sizing: border-box; }
    button { margin-top: 1.5rem; padding: 0.6rem 1.2rem; background: #0f2744; color: #fff; border: 0; cursor: pointer; }
    .err { color: #b00020; background: #fde8e8; padding: 0.75rem; border-radius: 6px; }
    .hint { font-size: 0.9rem; color: #444; margin-top: 0.25rem; }
    .box { background: #f0f4f8; border: 1px solid #c5d3e0; padding: 1rem; border-radius: 8px; margin: 1rem 0; font-size: 0.95rem; }
    .box ol { margin: 0.5rem 0 0 1.2rem; padding: 0; }
    .warn { color: #b00020; font-weight: 600; }
  </style>
</head>
<body>
  <h1>Database install</h1>
  <p>Use values from <strong>InfinityFree → MySQL Databases</strong> (not localhost phpMyAdmin).</p>

  <div class="box">
    <strong>In the InfinityFree panel you will see 4 lines — copy each exactly:</strong>
    <ol>
      <li><strong>MySQL Hostname</strong> → e.g. <code>sql308.infinityfree.com</code></li>
      <li><strong>MySQL Username</strong> → e.g. <code>if0_42101552_fbas</code> <span class="warn">(not if0_42101552 alone)</span></li>
      <li><strong>MySQL Password</strong> → the password you set when creating the database</li>
      <li><strong>MySQL Database Name</strong> → often the same as username, e.g. <code>if0_42101552_fbas</code></li>
    </ol>
  </div>

  <?php if ($error !== ''): ?>
  <p class="err"><?= install_h($error) ?></p>
  <?php endif; ?>

  <form method="post">
    <label for="db_host">MySQL hostname</label>
    <input name="db_host" id="db_host" required placeholder="sql205.infinityfree.com"
           value="<?= install_h($_POST['db_host'] ?? '') ?>">
    <p class="hint">From panel — looks like <code>sql###.infinityfree.com</code>, not your website URL.</p>

    <label for="db_user">MySQL username</label>
    <input name="db_user" id="db_user" required placeholder="if0_42101552_something"
           value="<?= install_h($_POST['db_user'] ?? '') ?>">
    <p class="hint">Usually longer than <code>if0_42101552</code> — copy the full username from the panel.</p>

    <label for="db_pass">MySQL password</label>
    <input name="db_pass" id="db_pass" type="password" required autocomplete="new-password">

    <label for="db_name">Database name</label>
    <input name="db_name" id="db_name" required placeholder="if0_42101552_something"
           value="<?= install_h($_POST['db_name'] ?? '') ?>">

    <button type="submit">Connect &amp; create tables</button>
  </form>
</body>
</html>
