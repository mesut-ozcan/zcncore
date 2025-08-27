<?php $layout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1>Admin Panel</h1>
  <p>Hoş geldin, admin!</p>
<?php $content = ob_get_clean(); include $layout; ?>
