<?php $layout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1>Şifre Sıfırlama</h1>
  <?php if (!empty($error)): ?>
    <div style="color:#a00; margin:.5rem 0;"><?= e($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($info)): ?>
    <div style="color:#070; margin:.5rem 0;"><?= $info ?></div>
  <?php endif; ?>
  <form method="post" action="/password/forgot" style="max-width:420px">
    <?= csrf_field() ?>
    <label>Email</label><br>
    <input type="email" name="email" required style="width:100%;padding:.5rem"><br><br>
    <button type="submit">Bağlantı Gönder</button>
    <a href="/login" style="margin-left:.5rem">Giriş</a>
  </form>
<?php $content = ob_get_clean(); include $layout; ?>
