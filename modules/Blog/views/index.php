<?php
/** @var array $items */
/** @var array $paginator */
$title = $title ?? 'Blog';
ob_start(); ?>
<h1><?= e($title) ?></h1>

<?php if (strtolower($_SESSION['user']['role'] ?? '') === 'admin'): ?>
  <p><a class="btn" href="/blog/create">+ Yeni Yazı</a></p>
<?php endif; ?>

<?php if (!$items): ?>
  <p>Henüz yazı yok.</p>
<?php else: ?>
  <ul class="list">
    <?php foreach ($items as $p): ?>
      <li>
        <a href="/blog/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a>
        <?php if (empty($p['published_at'])): ?><span class="badge">taslak</span><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
  <?= component('pagination', ['p'=>$paginator]) ?>
<?php endif; ?>

<?php $content = ob_get_clean(); include base_path('themes/default/views/layouts/main.php');
