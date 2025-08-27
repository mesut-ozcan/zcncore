<?php $layout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php $u = $user ?? null; ?>
<?php ob_start(); ?>
  <h1>Hesabım</h1>
  <?php if ($u): ?>
    <p><b>Ad:</b> <?= e($u['name']) ?></p>
    <p><b>Email:</b> <?= e($u['email']) ?></p>
    <p><b>Rol:</b> <?= e($u['role']) ?></p>
    <form method="post" action="/logout">
      <?= csrf_field() ?>
      <button type="submit">Çıkış Yap</button>
    </form>
  <?php else: ?>
    <p>Giriş yapılmamış.</p>
  <?php endif; ?>
<?php $content = ob_get_clean(); include $layout; ?>
