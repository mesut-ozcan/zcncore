<?php
/** @var array<array<string,mixed>> $posts */
$title = 'Blog';
ob_start();
?>
<h1>Blog</h1>

<?php if (!$posts): ?>
  <p>Henüz yazı yok.</p>
<?php else: ?>
  <ul class="post-list">
    <?php foreach ($posts as $p): ?>
      <li>
        <a href="/blog/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a>
        <?php if (!empty($p['published_at'])): ?>
          <small class="muted"> • <?= e($p['published_at']) ?></small>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php
$content = ob_get_clean();
include base_path('themes/default/views/layouts/main.php');