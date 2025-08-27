<?php $layout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1>Giriş Yap</h1>
  <?php if (!empty($error)): ?>
    <div style="color:#a00; margin:.5rem 0;"><?= e($error) ?></div>
  <?php endif; ?>
  <form method="post" action="/login" style="max-width:420px">
    <?= csrf_field() ?>
    <label>Email</label><br>
    <input type="email" name="email" required style="width:100%;padding:.5rem"><br><br>
    <label>Şifre</label><br>
    <input type="password" name="password" required style="width:100%;padding:.5rem"><br><br>
    <button type="submit">Giriş</button>
    <a href="/register" style="margin-left:.5rem">Kayıt Ol</a>
  </form>
<?php $content = ob_get_clean(); include $layout; ?>
