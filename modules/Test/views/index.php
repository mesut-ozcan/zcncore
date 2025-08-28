<?php
// modules/Test/views/index.php
$title = 'Test Module';
ob_start(); ?>
<h1>Test Module</h1>
<p>Bu sayfa modules/Test/views/index.php dosyasından geliyor.</p>
<?php $content = ob_get_clean(); include base_path('themes/default/views/layouts/main.php');