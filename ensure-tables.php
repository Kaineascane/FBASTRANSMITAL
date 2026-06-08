<?php
/**
 * One-time: create tables if missing. Delete after use.
 */
header('Content-Type: text/plain; charset=UTF-8');

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/bootstrap.php';

$conn = dbConnect($config);
if (!$conn) {
    echo "CONNECT FAILED: " . dbConnectLastError() . "\n";
    exit(1);
}

$sql = file_get_contents(__DIR__ . '/sql/setup-hosting.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $statement) {
    if ($statement === '' || stripos($statement, 'CREATE') === false) {
        continue;
    }
    if (!mysqli_query($conn, $statement)) {
        echo "SQL ERROR: " . mysqli_error($conn) . "\n";
        exit(1);
    }
}

echo "OK: Connected to if0_42101552_fbas and tables are ready.\n";
echo "Open: https://fbastransmittal.infinityfree.io/\n";
mysqli_close($conn);
