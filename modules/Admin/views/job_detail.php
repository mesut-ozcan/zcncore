<?php
$title = 'Admin • Failed Job Detail';
ob_start();
$job = $data['job'] ?? null;
?>
<h1>Failed Job Detail</h1>

<?php if (!$job): ?>
  <div class="alert danger">Kayıt bulunamadı.</div>
  <p><a class="btn" href="/admin/jobs/failed">Geri</a></p>
<?php else: ?>
  <p><a class="btn" href="/admin/jobs/failed">← Geri</a></p>

  <div class="card">
    <div class="card__body">
      <p><strong>ID:</strong> <code><?= e($job['id'] ?? '') ?></code></p>
      <p><strong>Sınıf:</strong> <code><?= e($job['class'] ?? '') ?></code></p>
      <p><strong>Attempts:</strong> <?= (int)($job['attempts'] ?? 0) ?> / <?= (int)($job['max'] ?? 0) ?></p>
      <p><strong>Oluşturma:</strong> <?= !empty($job['createdAt']) ? date('Y-m-d H:i:s', (int)$job['createdAt']) : '-' ?></p>
      <p><strong>Available At:</strong> <?= !empty($job['availableAt']) ? date('Y-m-d H:i:s', (int)$job['availableAt']) : '-' ?></p>
      <details>
        <summary><strong>Ham JSON</strong></summary>
        <pre><?= e(json_encode($job, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) ?></pre>
      </details>
    </div>
  </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include base_path('themes/' . config('app.theme', 'default') . '/views/layouts/main.php');