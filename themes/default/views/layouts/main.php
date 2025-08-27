<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= \Core\Head::render() ?>
  <link rel="stylesheet" href="/themes/default/assets/css/app.css">
</head>
<body>
  <header class="container">
    <a href="/" class="brand">ZCNCore</a>
    <nav>
      <a href="/">Home</a> · <a href="/pages/hello-zcn">Demo Page</a>
      <?php if (!empty($_SESSION['user'])): ?>
        · <a href="/account">Hesabım</a>
        <?php if (strtolower($_SESSION['user']['role'] ?? '') === 'admin'): ?>
          · <a href="/admin">Admin</a>
        <?php endif; ?>
        <form method="post" action="/logout" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" style="border:none;background:none;color:#06c;cursor:pointer;padding:0;margin-left:.5rem">Çıkış</button>
        </form>
      <?php else: ?>
        · <a href="/login">Giriş</a> · <a href="/register">Kayıt</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">
    <?= isset($content) ? $content : '' ?>
  </main>

  <footer class="container small muted">
    <hr>
    <p>ZCNCore • v<?= e(app()->version()) ?> • <?= number_format((microtime(true)-app()->startedAt()), 3) ?>s</p>
  </footer>
  <script src="/themes/default/assets/js/app.js"></script>
</body>
</html>
