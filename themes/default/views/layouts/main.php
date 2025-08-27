<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= head()->render() ?>
  <link rel="stylesheet" href="/themes/default/assets/css/app.css">
</head>
<body>
  <header class="container">
    <a href="/" class="brand">ZCNCore</a>
    <nav><a href="/">Home</a> · <a href="/pages/hello-zcn">Demo Page</a></nav>
  </header>
  <main class="container">
    <?= $content ?? '' ?>
  </main>
  <footer class="container small muted">
    <hr>
    <p>ZCNCore • v<?= e(app()->version()) ?> • <?= number_format((microtime(true)-app()->startedAt()), 3) ?>s</p>
  </footer>
  <script src="/themes/default/assets/js/app.js"></script>
</body>
</html>
