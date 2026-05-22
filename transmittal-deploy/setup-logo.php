<?php
/**
 * Install / update FBAS logo → assets/img/logo.png
 * Visit: http://localhost/transmittal-system/setup-logo.php
 */
$destDir = __DIR__ . '/assets/img';
$dest = $destDir . '/logo.png';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$searchPaths = [
    __DIR__ . '/assets/*fbas_logo*.png',
    __DIR__ . '/assets/*142714*.png',
    'C:/Users/Drei/.cursor/projects/c-Users-Drei-OneDrive-Desktop-Transmittal-Slip-Reciept/assets/*fbas_logo*.png',
    'C:/Users/Drei/.cursor/projects/c-Users-Drei-OneDrive-Desktop-Transmittal-Slip-Reciept/assets/*142714*.png',
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
