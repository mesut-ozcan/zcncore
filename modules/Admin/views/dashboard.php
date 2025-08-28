<?php
/** @var array $data */
$title = 'Admin • Dashboard';
ob_start();
?>
<h1>Admin • Dashboard</h1>

<?= component('flash') ?>

<section class="grid" style="margin-top:.75rem">
  <div class="card">
    <div class="muted small">Uygulama Sürümü</div>
    <div style="font-size:1.25rem;font-weight:800"><?= e($data['app_version']) ?></div>
  </div>
  <div class="card">
    <div class="muted small">PHP</div>
    <div style="font-size:1.25rem;font-weight:800"><?= e($data['php_version']) ?></div>
  </div>
  <div class="card">
    <div class="muted small">Uptime</div>
    <div style="font-size:1.25rem;font-weight:800"><?= number_format($data['uptime_sec']) ?> sn</div>
  </div>
  <div class="card">
    <div class="muted small">Cache Boyutu</div>
    <div style="font-size:1.25rem;font-weight:800"><?= number_format($data['cache_bytes']) ?> bayt</div>
  </div>
</section>

<hr style="margin:1.25rem 0">

<h2>İşlemler</h2>
<form method="post" action="/admin/cache/clear" onsubmit="return confirm('Cache temizlensin mi?')" style="margin:.5rem 0">
  <?= csrf_field() ?>
  <button type="submit" class="btn">Cache Temizle</button>
  <a href="/admin/logs" class="btn outline" style="margin-left:.5rem">Loglara Git</a>
</form>

<?php
$content = ob_get_clean();
include base_path('themes/' . config('app.theme', 'default') . '/views/layouts/main.php');