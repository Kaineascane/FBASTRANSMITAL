<?php
/**
 * One-time: copy logo into assets/img/logo.png — delete this file after go-live.
 */
$destDir = __DIR__ . '/assets/img';
$dest = $destDir . '/logo.png';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$searchPaths = [
    __DIR__ . '/assets/*fbas_logo*.png',
    __DIR__ . '/assets/logo-reference.png',
];

$src = null;
foreach ($searchPaths as $pattern) {
    $matches = glob($pattern);
    if ($matches) {
        usort($matches, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
        if (is_file($matches[0])) {
            $src = $matches[0];
            break;
        }
    }
}

if (!$src) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<p>Logo not found. Save your FBAS logo as <strong>assets/img/logo.png</strong></p>';
    exit;
}

if (copy($src, $dest)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<p>Logo updated: <strong>assets/img/logo.png</strong></p>';
    echo '<p><a href="print.php?id=1">Test print page</a> · <a href="index.php">Home</a></p>';
    echo '<p><img src="assets/img/logo.png" alt="FBAS Logo" style="max-width:120px"></p>';
} else {
    echo '<p>Copy failed. Check folder permissions.</p>';
}
