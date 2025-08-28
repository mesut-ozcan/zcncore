<?php
/** @var array $data */
$title = 'Admin • Dashboard';
ob_start();
?>
<h1>Admin • Dashboard</h1>

<?= component('flash') ?>

<div class="cards" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">
  <div class="card" style="border:1px solid #ddd;border-radius:12px;padding:1rem">
    <div class="muted">Uygulama Sürümü</div>
    <div><strong><?= e($data['app_version']) ?></strong></div>
  </div>
  <div class="card" style="border:1px solid #ddd;border-radius:12px;padding:1rem">
    <div class="muted">PHP</div>
    <div><strong><?= e($data['php_version']) ?></strong></div>
  </div>
  <div class="card" style="border:1px solid #ddd;border-radius:12px;padding:1rem">
    <div class="muted">Uptime</div>
    <div><strong><?= number_format($data['uptime_sec']) ?> s</strong></div>
  </div>
  <div class="card" style="border:1px solid #ddd;border-radius:12px;padding:1rem">
    <div class="muted">Cache Boyutu</div>
    <div><strong><?= number_format($data['cache_bytes']) ?> bayt</strong></div>
  </div>
</div>

<hr>
<h2>İşlemler</h2>
<form method="post" action="/admin/cache/clear" onsubmit="return confirm('Cache temizlensin mi?')">
  <?= csrf_field() ?>
  <button type="submit">Cache Temizle</button>
</form>

<?php
$content = ob_get_clean();
include base_path('themes/' . config('app.theme', 'default') . '/views/layouts/main.php');