<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');
$records = null;
$count = 0;

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($conn, "
        SELECT id, from_branch, to_branch, released_by, date_released, created_at
        FROM tbl_transmittal
        WHERE from_branch LIKE ?
           OR to_branch LIKE ?
           OR released_by LIKE ?
           OR CAST(id AS CHAR) LIKE ?
           OR date_released LIKE ?
        ORDER BY id DESC
        LIMIT 100
    ");
    mysqli_stmt_bind_param($stmt, 'sssss', $like, $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $records = mysqli_stmt_get_result($stmt);
    $count = $records ? mysqli_num_rows($records) : 0;
}

$pageTitle = 'Search — S.I Transmittal';
$headerTitle = 'SEARCH / REPRINT';
$headerActionHref = 'index.php';
$headerActionLabel = 'New Transmittal';
require __DIR__ . '/includes/head.php';
?>

<div class="card-panel">
  <form method="GET" role="search">
    <label class="form-label" for="q">Find transmittal</label>
    <div class="row g-2 align-items-stretch">
      <div class="col-12 col-md-9">
        <div class="search-input-wrap">
          <i class="bi bi-search search-icon" aria-hidden="true"></i>
          <input type="search" name="q" id="q" class="form-control"
                 value="<?= h($q) ?>"
                 placeholder="Branch, name, date, or ref #"
                 autocomplete="off">
        </div>
      </div>
      <div class="col-12 col-md-3">
        <button type="submit" class="btn btn-primary w-100 h-100">
          <i class="bi bi-search"></i> Search
        </button>
      </div>
    </div>
  </form>
</div>

<?php if ($q === ''): ?>
  <p class="page-intro">Search past transmittals by branch, person, date, or reference number, then reprint.</p>
<?php elseif ($count === 0): ?>
  <div class="card-panel empty-state">
    <i class="bi bi-inbox"></i>
    <p class="mb-0">No records found for &ldquo;<?= h($q) ?>&rdquo;.</p>
  </div>
<?php else: ?>
  <p class="page-intro mb-2"><?= (int) $count ?> result<?= $count === 1 ? '' : 's' ?> found</p>

  <div class="records-mobile">
    <?php
    mysqli_data_seek($records, 0);
    while ($r = mysqli_fetch_assoc($records)):
    ?>
    <article class="record-card">
      <div class="record-card-header">
        <span class="record-ref">#<?= (int) $r['id'] ?></span>
        <span class="text-muted small"><?= h($r['date_released']) ?></span>
      </div>
      <dl>
        <dt>From</dt><dd><?= h($r['from_branch']) ?></dd>
        <dt>To</dt><dd><?= h($r['to_branch']) ?></dd>
        <dt>Released by</dt><dd><?= h($r['released_by']) ?></dd>
      </dl>
      <a href="print.php?id=<?= (int) $r['id'] ?>" class="btn btn-primary btn-reprint" target="_blank" rel="noopener">
        <i class="bi bi-printer"></i> Reprint
      </a>
    </article>
    <?php endwhile; ?>
  </div>

  <div class="card-panel p-0 overflow-hidden records-desktop">
    <div class="table-responsive">
      <table class="table table-hover mb-0 table-records">
        <thead>
          <tr>
            <th>Ref #</th>
            <th>From</th>
            <th>To</th>
            <th>Released by</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php
          mysqli_data_seek($records, 0);
          while ($r = mysqli_fetch_assoc($records)):
          ?>
          <tr>
            <td><strong><?= (int) $r['id'] ?></strong></td>
            <td><?= h($r['from_branch']) ?></td>
            <td><?= h($r['to_branch']) ?></td>
            <td><?= h($r['released_by']) ?></td>
            <td><?= h($r['date_released']) ?></td>
            <td>
              <a href="print.php?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-primary" target="_blank" rel="noopener">
                <i class="bi bi-printer"></i> Reprint
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
