<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title><?= isset($title) ? e($title) : 'ZCNCore' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= \Core\Head::render() ?>
  <link rel="stylesheet" href="<?= e(mix('/themes/neon/assets/css/app.css')) ?>">
</head>
<body>
  <header class="container">
    <a href="/" class="brand"><?= e(config('app.name','ZCNCore')) ?></a>
    <nav><a href="/">Home</a></nav>
  </header>
  <main class="container">
    <?= $content ?? '' ?>
  </main>
  <footer class="container small muted">
    <hr>
    <p><?= e(config('app.name','ZCNCore')) ?> • v<?= e(app()->version()) ?></p>
  </footer>
  <script src="<?= e(mix('/themes/neon/assets/js/app.js')) ?>"></script>
</body>
</html>