<?php $layout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1>Log Viewer</h1>
  <?php if (!$file_exists): ?>
    <p>Log dosyası bulunamadı: <code>storage/logs/app.log</code></p>
  <?php else: ?>
    <form method="get" action="/admin/logs" style="margin: .5rem 0">
      <label>Son satır sayısı:</label>
      <input type="number" name="n" value="<?= e((string)$n) ?>" min="10" max="5000" style="width:100px">
      <label style="margin-left:.5rem">Filtre (q):</label>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="[ERROR] gibi">
      <button type="submit">Göster</button>
      <a href="/admin/logs/download" style="margin-left:.5rem">İndir (app.log)</a>
    </form>
    <pre style="background:#111;color:#ddd;padding:1rem;overflow:auto;max-height:70vh;white-space:pre-wrap"><?= e($content) ?></pre>
  <?php endif; ?>
<?php $content = ob_get_clean(); include $layout; ?>
