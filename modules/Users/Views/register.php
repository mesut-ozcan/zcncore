<?php $layout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1>Kayıt Ol</h1>
  <?php if (!empty($error)): ?>
    <div style="color:#a00; margin:.5rem 0;"><?= e($error) ?></div>
  <?php endif; ?>
  <form method="post" action="/register" style="max-width:420px">
    <?= csrf_field() ?>
    <label>Ad</label><br>
    <input type="text" name="name" style="width:100%;padding:.5rem"><br><br>
    <label>Email</label><br>
    <input type="email" name="email" required style="width:100%;padding:.5rem"><br><br>
    <label>Şifre</label><br>
    <input type="password" name="password" required style="width:100%;padding:.5rem"><br><br>
    <label>Şifre (Tekrar)</label><br>
    <input type="password" name="password_confirmation" required style="width:100%;padding:.5rem"><br><br>
    <button type="submit">Kayıt Ol</button>
    <a href="/login" style="margin-left:.5rem">Giriş Yap</a>
  </form>
<?php $content = ob_get_clean(); include $layout; ?>
