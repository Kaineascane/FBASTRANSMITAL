<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

$flash = flashConsume();
$errors = $flash['errors'];
$old = $flash['old'];

$next = getNextSeries($conn);
$today = date('Y-m-d');
$lastSiEnd = max(0, (int) $next['si'] - 1);

$fromVal = $old['from_branch'] ?? '';
$toVal = $old['to_branch'] ?? '';
$releasedVal = $old['released_by'] ?? '';
$dateVal = $old['date_released'] ?? $today;
$padVal = isset($old['starting_pad']) ? (int) $old['starting_pad'] : (int) $next['pad'];
$siVal = isset($old['starting_si']) ? (int) $old['starting_si'] : (int) $next['si'];
$totalVal = isset($old['total_pads']) ? (int) $old['total_pads'] : '';

$pageTitle = 'New Transmittal — S.I Transmittal';
$headerTitle = 'S.I TRANSMITTAL SYSTEM';
$headerActionHref = 'search.php';
$headerActionLabel = 'Search / Reprint';
require __DIR__ . '/includes/head.php';
?>

<?php if ($errors !== []): ?>
<div class="alert alert-error" role="alert">
  <strong><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following:</strong>
  <ul class="mb-0 mt-2">
    <?php foreach ($errors as $err): ?>
    <li><?= h($err) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="card-panel">
  <form action="save.php" method="POST" id="transmittalForm">

    <section class="form-section">
      <h2 class="form-section-title"><i class="bi bi-building"></i> Branch details</h2>
      <div class="row g-3">
        <div class="col-12 col-sm-6">
          <label class="form-label" for="from_branch">From</label>
          <input type="text" name="from_branch" id="from_branch" class="form-control"
                 value="<?= h($fromVal) ?>" placeholder="e.g. MAIN OFFICE (LIPA)" required autocomplete="organization">
        </div>
        <div class="col-12 col-sm-6">
          <label class="form-label" for="to_branch">To</label>
          <input type="text" name="to_branch" id="to_branch" class="form-control"
                 value="<?= h($toVal) ?>" placeholder="e.g. LIPA (LUIS LOYOLA)" required autocomplete="organization">
        </div>
        <div class="col-12 col-sm-6">
          <label class="form-label" for="released_by">Released by</label>
          <input type="text" name="released_by" id="released_by" class="form-control"
                 value="<?= h($releasedVal) ?>" placeholder="Full name" required autocomplete="name">
        </div>
        <div class="col-12 col-sm-6">
          <label class="form-label" for="date_released">Date released</label>
          <input type="date" name="date_released" id="date_released" class="form-control"
                 value="<?= h($dateVal) ?>" required>
        </div>
      </div>
    </section>

    <section class="form-section">
      <h2 class="form-section-title"><i class="bi bi-hash"></i> Pad &amp; S.I series</h2>
      <div class="row g-3">
        <div class="col-12 col-sm-6">
          <label class="form-label" for="starting_pad">Starting pad no.</label>
          <input type="number" name="starting_pad" id="starting_pad" class="form-control"
                 value="<?= $padVal ?>" min="1" required inputmode="numeric">
          <div class="hint-auto">
            <i class="bi bi-lightning-charge"></i>
            Auto from last record — next pad <strong><?= (int) $next['pad'] ?></strong>
          </div>
        </div>
        <div class="col-12 col-sm-6">
          <label class="form-label" for="starting_si">Starting S.I no.</label>
          <input type="number" name="starting_si" id="starting_si" class="form-control"
                 value="<?= $siVal ?>" min="1" required inputmode="numeric">
          <div class="hint-auto">
            <i class="bi bi-lightning-charge"></i>
            After last end <strong><?= $lastSiEnd ?></strong> → <strong><?= (int) $next['si'] ?></strong>
          </div>
        </div>
        <div class="col-12 col-sm-6">
          <label class="form-label" for="total_pads">Total pads</label>
          <input type="number" name="total_pads" id="total_pads" class="form-control"
                 value="<?= $totalVal !== '' ? (int) $totalVal : '' ?>"
                 min="1" max="500" placeholder="e.g. 5" required inputmode="numeric">
        </div>
      </div>

      <div class="live-preview" id="livePreview" aria-live="polite">
        Enter total pads to preview S.I ranges…
      </div>
    </section>

    <div class="info-box">
      <span class="info-box-icon"><i class="bi bi-info-lg"></i></span>
      <div>
        <strong>50 S.I numbers per pad</strong> — e.g. 48751–48800, then 48801–48850.
        Pad no. and series increment automatically on save.
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary btn-lg">
        <i class="bi bi-printer"></i> Generate &amp; Save
      </button>
    </div>
  </form>
</div>

<script>
(function () {
  const pad = document.getElementById('starting_pad');
  const si = document.getElementById('starting_si');
  const total = document.getElementById('total_pads');
  const preview = document.getElementById('livePreview');
  const PER_PAD = 50;

  function updatePreview() {
    const p = parseInt(pad.value, 10) || 0;
    const s = parseInt(si.value, 10) || 0;
    const n = parseInt(total.value, 10) || 0;
    if (!n || !p || !s) {
      preview.textContent = 'Enter total pads to preview S.I ranges…';
      return;
    }
    const lines = [];
    let curPad = p, curSi = s;
    const show = Math.min(n, 3);
    for (let i = 0; i < show; i++) {
      const end = curSi + PER_PAD - 1;
      lines.push('Pad ' + curPad + ': ' + curSi + '–' + end);
      curPad++;
      curSi = end + 1;
    }
    if (n > show) lines.push('… +' + (n - show) + ' more pad(s)');
    preview.innerHTML = '<strong>Preview:</strong> ' + lines.join(' · ');
  }

  [pad, si, total].forEach(function (el) {
    el.addEventListener('input', updatePreview);
  });
  updatePreview();

  <?php if ($errors !== []): ?>
  document.querySelector('.alert-error')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  <?php endif; ?>
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
