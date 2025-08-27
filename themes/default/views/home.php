<?php $mainLayout = __DIR__ . '/layouts/main.php'; ?>
<?php ob_start(); ?>
  <h1>Theme Override: Home</h1>
  <p>Bu görünüm <strong>themes/default/views/home.php</strong> ile app/Views/home.php'yi override eder.</p>
<?php $content = ob_get_clean(); include $mainLayout; ?>
