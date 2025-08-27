<?php $layout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1>Yeni Şifre</h1>
  <?php if (!empty($error)): ?>
    <div style="color:#a00; margin:.5rem 0;"><?= e($error) ?></div>
  <?php endif; ?>
  <form method="post" action="/password/reset/<?= e($token ?? '') ?>" style="max-width:420px">
    <?= csrf_field() ?>
    <label>Yeni Şifre</label><br>
    <input type="password" name="password" required style="width:100%;padding:.5rem"><br><br>
    <label>Yeni Şifre (Tekrar)</label><br>
    <input type="password" name="password_confirmation" required style="width:100%;padding:.5rem"><br><br>
    <button type="submit">Şifreyi Güncelle</button>
  </form>
<?php $content = ob_get_clean(); include $layout; ?>
