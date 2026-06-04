<?php

/**
 * Render one FBAS S.I. Transmittal Slip (matches official form layout).
 *
 * @param array<string, mixed> $row
 * @param list<array<string, mixed>|null> $tableRows
 */
function renderFbasSlip(
    array $row,
    array $tableRows,
    string $copyLabel,
    int $pageNum,
    int $totalPages,
    int $startNo
): void {
    $dateReleased = formatTransmittalDate((string) $row['date_released']);
    $logoUrl = getLogoUrl();
    ?>
<article class="fbas-slip">
  <div class="fbas-doc-ref">ORTS.REV003-05032024</div>

  <header class="fbas-header">
    <div class="fbas-logo" aria-hidden="true">
      <?php if ($logoUrl): ?>
      <img src="<?= h($logoUrl) ?>" alt="FBAS Insurance Agency Co." class="fbas-logo-img">
      <?php else: ?>
      <div class="fbas-logo-placeholder">FBAS</div>
      <?php endif; ?>
    </div>
    <div class="fbas-company">
      <div class="fbas-company-name">FBAS INSURANCE AGENCY CO.</div>
      <div class="fbas-company-addr">126 Kumintang Ilaya, Batangas City, Capital, Batangas</div>
      <div class="fbas-slip-title">S.I. TRANSMITTAL SLIP</div>
    </div>
  </header>

  <div class="fbas-meta">
    <div class="fbas-field">
      <span class="fbas-field-label">FROM:</span>
      <span class="fbas-field-value"><?= h($row['from_branch']) ?></span>
    </div>
    <div class="fbas-field">
      <span class="fbas-field-label">TO:</span>
      <span class="fbas-field-value"><?= h($row['to_branch']) ?></span>
    </div>
    <div class="fbas-field">
      <span class="fbas-field-label">DATE RELEASED:</span>
      <span class="fbas-field-value"><?= h($dateReleased) ?></span>
    </div>
    <div class="fbas-field fbas-field-signature">
      <span class="fbas-field-label">RELEASED BY:</span>
      <span class="fbas-field-value"><?= h($row['released_by']) ?></span>
      <div class="fbas-sig-caption">Signature Over Printed Name</div>
    </div>
  </div>

  <div class="fbas-table-block">
    <table class="fbas-table fbas-table-20">
      <thead>
        <tr>
          <th class="col-no">NO.</th>
          <th class="col-pad">PAD NO.</th>
          <th class="col-si">S.I. SERIES NO.</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tableRows as $i => $detail): ?>
        <tr>
          <td><?= $startNo + $i ?></td>
          <td><?= $detail ? (int) $detail['pad_no'] : '&nbsp;' ?></td>
          <td><?php
            if ($detail) {
                echo (int) $detail['si_start'] . '-' . (int) $detail['si_end'];
            } else {
                echo '&nbsp;';
            }
          ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <aside class="fbas-side-boxes">
      <div class="fbas-side-page">
        PAGE <span class="fbas-underline"><?= (int) $pageNum ?></span>
        OF <span class="fbas-underline"><?= (int) $totalPages ?></span>
      </div>
      <div class="fbas-side-copy"><?= h($copyLabel) ?></div>
    </aside>
  </div>

  <footer class="fbas-footer">
    <div class="fbas-footer-row">
      <div class="fbas-footer-sig">
        <div class="fbas-footer-label">DELIVERED BY:</div>
        <div class="fbas-footer-sign-area"></div>
        <div class="fbas-sig-caption">Signature Over Printed Name</div>
      </div>
      <div class="fbas-footer-date">
        <span class="fbas-footer-date-label">DATE:</span>
        <div class="fbas-footer-date-line"></div>
      </div>
    </div>
    <div class="fbas-footer-row">
      <div class="fbas-footer-sig">
        <div class="fbas-footer-label">RECEIVED BY:</div>
        <div class="fbas-footer-sign-area"></div>
        <div class="fbas-sig-caption">Signature Over Printed Name</div>
      </div>
      <div class="fbas-footer-date">
        <span class="fbas-footer-date-label">DATE:</span>
        <div class="fbas-footer-date-line"></div>
      </div>
    </div>
  </footer>
</article>
    <?php
}
