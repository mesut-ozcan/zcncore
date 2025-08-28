<?php
/** @var array{files:array,selected:?string,tail:array} $data */
$title = 'Admin • Logs';
ob_start();
?>
<h1>Loglar</h1>

<form method="get" action="/admin/logs" style="margin-bottom:1rem">
  <label for="file">Dosya: </label>
  <select id="file" name="file" onchange="this.form.submit()">
    <?php foreach ($data['files'] as $f): ?>
      <option value="<?= e($f) ?>" <?= $f === $data['selected'] ? 'selected' : '' ?>>
        <?= e($f) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <?php if ($data['selected']): ?>
    <a href="/admin/logs/download?file=<?= urlencode($data['selected']) ?>">İndir</a>
  <?php endif; ?>
</form>

<pre style="background:#111;color:#0f0;padding:1rem;border-radius:8px;max-height:60vh;overflow:auto">
<?php foreach ($data['tail'] as $line): ?>
<?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "\n" ?>
<?php endforeach; ?>
</pre>

<?php
$content = ob_get_clean();
include base_path('themes/' . config('app.theme', 'default') . '/views/layouts/main.php');