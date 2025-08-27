<?php $mainLayout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1>Page: <?= e($slug) ?></h1>
  <p>Bu sayfa <strong>Modules/Pages/Views/page.php</strong> dosyasından render edildi.</p>
<?php $content = ob_get_clean(); include $mainLayout; ?>
