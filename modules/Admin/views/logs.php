<?php
/** @var array{files:array,selected:?string,tail:array} $data */
$title = 'Admin • Logs';
ob_start();
?>
<h1>Loglar</h1>

<form method="get" action="/admin/logs" style="display:flex;gap:.5rem;align-items:center;margin-bottom:1rem">
  <label for="file" class="muted small">Dosya</label>
  <select id="file" name="file">
    <?php foreach ($data['files'] as $f): ?>
      <option value="<?= e($f) ?>" <?= $f === $data['selected'] ? 'selected' : '' ?>>
        <?= e($f) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button class="btn" style="margin-left:.25rem">Aç</button>
  <?php if ($data['selected']): ?>
    <a class="btn outline" href="/admin/logs/download?file=<?= urlencode($data['selected']) ?>">İndir</a>
  <?php endif; ?>
  <div style="flex:1"></div>
  <input id="logFilter" class="input" type="text" placeholder="Satır filtrele... (örn: ERROR|WARNING)" style="max-width:320px">
</form>

<pre id="logTail" class="log">
<?php foreach ($data['tail'] as $line): ?>
<?= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "\n" ?>
<?php endforeach; ?>
</pre>

<script>
(function(){
  const input = document.getElementById('logFilter');
  const pre   = document.getElementById('logTail');
  if(!input || !pre) return;
  const orig  = pre.textContent.split('\n');

  function apply(){
    const q = input.value.trim();
    if(!q){ pre.textContent = orig.join('\n'); return; }
    let re;
    try { re = new RegExp(q, 'i'); } catch(e){ re = null; }
    const out = re ? orig.filter(l => re.test(l)) : orig.filter(l => l.includes(q));
    pre.textContent = out.join('\n');
  }
  input.addEventListener('input', apply);
})();
</script>

<?php
$content = ob_get_clean();
include base_path('themes/' . config('app.theme', 'default') . '/views/layouts/main.php');