<?php
/**
 * Beklenen $page, $per, $total, $last, $html (paginate() çıktısı)
 * En kolayı: component('pagination', $p) — $p, paginate() dönen dizi
 */
$page  = $page  ?? 1;
$per   = $per   ?? 10;
$total = $total ?? 0;
$last  = $last  ?? 1;
$html  = $html  ?? '';

if ($last > 1): ?>
<div class="zcn-pagination" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
  <span style="opacity:.7">Sayfa <?= (int)$page ?> / <?= (int)$last ?> — Toplam <?= (int)$total ?></span>
  <?= $html ?>
</div>
<?php endif; ?>
