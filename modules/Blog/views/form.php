<?php
/** @var array $post */
/** @var string $mode */
$title = $title ?? ($mode==='edit'?'Yazıyı Düzenle':'Yeni Yazı');
$action = $mode==='edit' ? "/blog/".e($post['slug'])."/update" : "/blog/store";
ob_start(); ?>
<h1><?= e($title) ?></h1>

<form method="post" action="<?= $action ?>">
  <?= csrf_field() ?>

  <div class="field">
    <label>Başlık</label>
    <input type="text" name="title" value="<?= e(old('title', $post['title']??'')) ?>">
    <?php if ($e = errors('title')): ?><div class="error"><?= e($e) ?></div><?php endif; ?>
  </div>

  <div class="field">
    <label>İçerik</label>
    <textarea name="body" rows="10"><?= e(old('body', $post['body']??'')) ?></textarea>
    <?php if ($e = errors('body')): ?><div class="error"><?= e($e) ?></div><?php endif; ?>
  </div>

  <div class="field">
    <label><input type="checkbox" name="publish" value="1" <?= !empty($post['published_at']) ? 'checked' : '' ?>> Yayınla</label>
  </div>

  <div class="actions">
    <button class="btn primary" type="submit"><?= $mode==='edit'?'Güncelle':'Kaydet' ?></button>
    <a class="btn" href="/blog">Vazgeç</a>
  </div>
</form>

<?php $content = ob_get_clean(); include base_path('themes/default/views/layouts/main.php');
