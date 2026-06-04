<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$validation = validateTransmittalInput($_POST);

if (!$validation['valid']) {
    flashSetErrors($validation['errors'], $validation['data']);
    header('Location: index.php');
    exit;
}

$d = $validation['data'];
$from = $d['from_branch'];
$to = $d['to_branch'];
$released = $d['released_by'];
$date = $d['date_released'];
$startingPad = (int) $d['starting_pad'];
$startingSI = (int) $d['starting_si'];
$totalPads = (int) $d['total_pads'];

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare($conn, '
        INSERT INTO tbl_transmittal (from_branch, to_branch, released_by, date_released)
        VALUES (?, ?, ?, ?)
    ');
    mysqli_stmt_bind_param($stmt, 'ssss', $from, $to, $released, $date);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $transmittalId = (int) mysqli_insert_id($conn);

    $detailStmt = mysqli_prepare($conn, '
        INSERT INTO tbl_transmittal_details (transmittal_id, pad_no, si_start, si_end)
        VALUES (?, ?, ?, ?)
    ');

    $pad = $startingPad;
    $siStart = $startingSI;

    for ($i = 0; $i < $totalPads; $i++) {
        $siEnd = $siStart + SI_PER_PAD - 1;
        mysqli_stmt_bind_param($detailStmt, 'iiii', $transmittalId, $pad, $siStart, $siEnd);
        mysqli_stmt_execute($detailStmt);
        $pad++;
        $siStart = $siEnd + 1;
    }

    mysqli_stmt_close($detailStmt);
    mysqli_commit($conn);

    header('Location: print.php?id=' . $transmittalId);
    exit;
} catch (Throwable $e) {
    mysqli_rollback($conn);
    $config = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];
    $debug = (bool) ($config['debug'] ?? false);
    $errors = ['Could not save to database. Please try again.'];
    if ($debug) {
        $errors[] = 'Details: ' . $e->getMessage();
    }
    flashSetErrors($errors, $validation['data']);
    header('Location: index.php');
    exit;
}
