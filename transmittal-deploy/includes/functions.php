<?php

/** S.I count per pad (inclusive range: start .. start+49) */
define('SI_PER_PAD', 50);

/** Table rows per printed slip page */
define('SLIP_ROWS_PER_PAGE', 20);

function getNextSeries(mysqli $conn): array
{
    $pad = 1;
    $si = 1;

    $padResult = mysqli_query($conn, 'SELECT MAX(pad_no) AS last_pad FROM tbl_transmittal_details');
    if ($padResult && ($row = mysqli_fetch_assoc($padResult)) && $row['last_pad'] !== null) {
        $pad = (int) $row['last_pad'] + 1;
    }

    $siResult = mysqli_query($conn, 'SELECT MAX(si_end) AS last_si FROM tbl_transmittal_details');
    if ($siResult && ($row = mysqli_fetch_assoc($siResult)) && $row['last_si'] !== null) {
        $si = (int) $row['last_si'] + 1;
    }

    return ['pad' => $pad, 'si' => $si];
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flashStart(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** @param list<string> $errors */
function flashSetErrors(array $errors, array $oldInput): void
{
    flashStart();
    $_SESSION['flash_errors'] = $errors;
    $_SESSION['flash_old'] = $oldInput;
}

/** @return array{errors: list<string>, old: array<string, mixed>} */
function flashConsume(): array
{
    flashStart();
    $errors = $_SESSION['flash_errors'] ?? [];
    $old = $_SESSION['flash_old'] ?? [];
    unset($_SESSION['flash_errors'], $_SESSION['flash_old']);

    return ['errors' => is_array($errors) ? $errors : [], 'old' => is_array($old) ? $old : []];
}

/**
 * @param array<string, mixed> $post
 * @return array{valid: bool, errors: list<string>, data: array<string, mixed>}
 */
function validateTransmittalInput(array $post): array
{
    $from = trim((string) ($post['from_branch'] ?? ''));
    $to = trim((string) ($post['to_branch'] ?? ''));
    $released = trim((string) ($post['released_by'] ?? ''));
    $date = trim((string) ($post['date_released'] ?? ''));
    $startingPad = (int) ($post['starting_pad'] ?? 0);
    $startingSI = (int) ($post['starting_si'] ?? 0);
    $totalPads = (int) ($post['total_pads'] ?? 0);

    $errors = [];

    if ($from === '') {
        $errors[] = 'FROM branch is required.';
    }
    if ($to === '') {
        $errors[] = 'TO branch is required.';
    }
    if ($released === '') {
        $errors[] = 'RELEASED BY is required.';
    }
    if ($date === '') {
        $errors[] = 'DATE RELEASED is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
        $errors[] = 'DATE RELEASED must be a valid date.';
    }
    if ($startingPad < 1) {
        $errors[] = 'STARTING PAD NO must be at least 1.';
    }
    if ($startingSI < 1) {
        $errors[] = 'STARTING S.I NO must be at least 1.';
    }
    if ($totalPads < 1) {
        $errors[] = 'TOTAL PADS is required (minimum 1).';
    } elseif ($totalPads > 500) {
        $errors[] = 'TOTAL PADS cannot exceed 500.';
    }

    return [
        'valid' => $errors === [],
        'errors' => $errors,
        'data' => [
            'from_branch' => $from,
            'to_branch' => $to,
            'released_by' => $released,
            'date_released' => $date,
            'starting_pad' => $startingPad,
            'starting_si' => $startingSI,
            'total_pads' => $totalPads,
        ],
    ];
}

function formatTransmittalDate(string $date): string
{
    return strtoupper(date('F j, Y', strtotime($date)));
}

/** Path/URL to FBAS logo for print layout, or null if missing */
function getLogoUrl(): ?string
{
    $dir = __DIR__ . '/../assets/img';
    foreach (['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp'] as $file) {
        if (is_file($dir . '/' . $file)) {
            return 'assets/img/' . $file;
        }
    }

    $cursorPatterns = [
        __DIR__ . '/../assets/*fbas_logo*.png',
        'C:/Users/Drei/.cursor/projects/c-Users-Drei-OneDrive-Desktop-Transmittal-Slip-Reciept/assets/*fbas_logo*.png',
        __DIR__ . '/../assets/*142714*.png',
        'C:/Users/Drei/.cursor/projects/c-Users-Drei-OneDrive-Desktop-Transmittal-Slip-Reciept/assets/*142714*.png',
    ];
    foreach ($cursorPatterns as $pattern) {
        $matches = glob($pattern);
        if (!$matches) {
            continue;
        }
        usort($matches, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
        if (!is_file($matches[0])) {
            continue;
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $dest = $dir . '/logo.png';
        if (@copy($matches[0], $dest) || is_file($dest)) {
            return 'assets/img/logo.png';
        }
    }

    return null;
}

/** @return list<array<string, mixed>> */
function getAllDetailRows(mysqli_result $details): array
{
    $rows = [];
    while ($d = mysqli_fetch_assoc($details)) {
        $rows[] = $d;
    }

    return $rows;
}

/**
 * Split detail rows into print pages (20 rows each, padded with blanks).
 *
 * @param list<array<string, mixed>> $allRows
 * @return list<array{rows: list<array<string, mixed>|null>, page: int, total: int, startNo: int}>
 */
function paginateSlipRows(array $allRows, int $perPage = SLIP_ROWS_PER_PAGE): array
{
    $count = count($allRows);
    $totalPages = max(1, (int) ceil($count / $perPage));

    if ($count === 0) {
        $allRows = [];
        $totalPages = 1;
    }

    $pages = [];
    for ($p = 0; $p < $totalPages; $p++) {
        $chunk = array_slice($allRows, $p * $perPage, $perPage);
        while (count($chunk) < $perPage) {
            $chunk[] = null;
        }
        $pages[] = [
            'rows' => $chunk,
            'page' => $p + 1,
            'total' => $totalPages,
            'startNo' => $p * $perPage + 1,
        ];
    }

    return $pages;
}
