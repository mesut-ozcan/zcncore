<?php
$title = 'Admin • Failed Jobs';
ob_start();
?>
<h1>Failed Jobs</h1>

<?= component('flash') ?>

<?php if (empty($data['jobs'])): ?>
  <div class="alert success">Şu an failed queue boş görünüyor. 🎉</div>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Sınıf</th>
        <th>Attempts</th>
        <th>Oluşturma</th>
        <th style="width:280px">İşlem</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($data['jobs'] as $j): ?>
      <tr>
        <td class="small"><?= e($j['id']) ?></td>
        <td><code><?= e($j['class']) ?></code></td>
        <td><?= e($j['attempts']) ?> / <?= e($j['max']) ?></td>
        <td class="small"><?= $j['createdAt'] ? date('Y-m-d H:i:s', $j['createdAt']) : '-' ?></td>
        <td>
          <a class="btn outline" href="/admin/jobs/failed/detail?id=<?= urlencode($j['id']) ?>">Detay</a>
          <form method="post" action="/admin/jobs/failed/retry" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($j['id']) ?>">
            <button class="btn" type="submit">Retry</button>
          </form>
          <form method="post" action="/admin/jobs/failed/delete" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= e($j['id']) ?>">
            <button class="btn danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php
$content = ob_get_clean();
include base_path('themes/' . config('app.theme', 'default') . '/views/layouts/main.php');