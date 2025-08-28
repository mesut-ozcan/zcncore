<?php
// modules/Blog/views/index.php
$title = 'Blog Module';
ob_start(); ?>
<h1>Blog Module</h1>
<p>Bu sayfa modules/Blog/views/index.php dosyasından geliyor.</p>
<?php $content = ob_get_clean(); include base_path('themes/default/views/layouts/main.php');