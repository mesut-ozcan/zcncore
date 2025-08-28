<?php
// themes/neon/views/home.php
$title = 'Welcome - neon';
ob_start(); ?>
<h1>Yeni Tema: neon</h1>
<p>Bu sayfa themes/neon/views/home.php dosyasından geliyor.</p>
<?php $content = ob_get_clean(); include base_path('themes/neon/views/layouts/main.php');