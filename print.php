<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/print-slip.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: index.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT * FROM tbl_transmittal WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die('Transmittal not found. <a href="search.php">Search records</a>');
}

$detailStmt = mysqli_prepare($conn, '
    SELECT * FROM tbl_transmittal_details
    WHERE transmittal_id = ?
    ORDER BY pad_no ASC
');
mysqli_stmt_bind_param($detailStmt, 'i', $id);
mysqli_stmt_execute($detailStmt);
$details = mysqli_stmt_get_result($detailStmt);
$allRows = getAllDetailRows($details);
$pages = paginateSlipRows($allRows);
mysqli_stmt_close($detailStmt);

$copies = ["ADMIN'S COPY", "ADMIN ASSISTANT'S COPY"];
$totalPads = count($allRows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Transmittal #<?= $id ?> — FBAS</title>
  <link href="css/print.css" rel="stylesheet">
</head>
<body class="print-body">

<nav class="no-print print-toolbar">
  <button type="button" onclick="window.print()" class="print-btn">Print</button>
  <a href="index.php">New Transmittal</a>
  <a href="search.php">Search / Reprint</a>
  <span class="print-hint">
    Landscape · <?= (int) count($pages) ?> sheet<?= count($pages) === 1 ? '' : 's' ?>
    · <?= (int) $totalPads ?> pad<?= $totalPads === 1 ? '' : 's' ?>
    · 20 NO. per page
  </span>
</nav>

<?php foreach ($pages as $pageIndex => $pageData): ?>
<div class="print-page<?= $pageIndex < count($pages) - 1 ? ' print-page-break' : '' ?>">
  <?php foreach ($copies as $copyIndex => $copyLabel): ?>
    <?php renderFbasSlip(
        $row,
        $pageData['rows'],
        $copyLabel,
        $pageData['page'],
        $pageData['total'],
        $pageData['startNo']
    ); ?>
    <?php if ($copyIndex === 0): ?>
      <div class="print-cut-line" aria-hidden="true"></div>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>

<script>
  window.addEventListener('load', function () {
    if (!sessionStorage.getItem('printed_<?= $id ?>')) {
      sessionStorage.setItem('printed_<?= $id ?>', '1');
      window.print();
    }
  });
</script>
</body>
</html>
