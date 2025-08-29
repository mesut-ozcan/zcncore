<?php
/** @var array $post */
$title = $title ?? $post['title'] ?? 'Yazı';
ob_start(); ?>
<h1><?= e($post['title']) ?></h1>
<?php if (strtolower($_SESSION['user']['role'] ?? '') === 'admin'): ?>
  <p>
    <a class="btn" href="/blog/<?= e($post['slug']) ?>/edit">Düzenle</a>
    <form method="post" action="/blog/<?= e($post['slug']) ?>/delete" style="display:inline">
      <?= csrf_field() ?>
      <button class="btn danger" onclick="return confirm('Silinsin mi?')">Sil</button>
    </form>
  </p>
<?php endif; ?>
<article class="prose">
  <?= nl2br(e($post['body'])) ?>
</article>
<?php $content = ob_get_clean(); include base_path('themes/default/views/layouts/main.php');
