<?php $mainLayout = base_path('themes/'.config('app.theme','default').'/views/layouts/main.php'); ?>
<?php ob_start(); ?>
  <h1><?= e($title ?? 'ZCNCore') ?></h1>
  <p>Bu sayfa <strong>app/Views/home.php</strong> dosyasından geliyor.</p>
  <p>Modül örneği için: <a href="/pages/hello-zcn">/pages/hello-zcn</a></p>
<?php $content = ob_get_clean(); include $mainLayout; ?>
