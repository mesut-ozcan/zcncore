<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
    // OG/Twitter varsayılanlarını doldur (yoksa)
    $base = rtrim(config('app.url',''), '/');
    $title = 'ZCNCore';
    $desc  = 'Minimal Saf PHP çekirdek framework — module/theme ile esnek.';
    $canon = $base ? $base . ($_SERVER['REQUEST_URI'] ?? '/') : '';

    \Core\Head::addProperty('og:type', 'website');
    if ($canon) \Core\Head::addProperty('og:url', $canon);
    if ($title) {
        \Core\Head::addProperty('og:title', $title);
        \Core\Head::addProperty('twitter:title', $title);
    }
    if ($desc) {
        \Core\Head::addProperty('og:description', $desc);
        \Core\Head::addProperty('twitter:description', $desc);
    }
    \Core\Head::addProperty('twitter:card', 'summary');

    echo \Core\Head::render();
  ?>
  <link rel="stylesheet" href="<?= e(asset('/themes/default/assets/css/app.css')) ?>">
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
          · <a href="/admin/logs">Logs</a>
        <?php endif; ?>
        <form method="post" action="/logout" style="display:inline">
          <?= csrf_field() ?>
          <button type="submit" style="border:none;background:none;color:#06c;cursor:pointer;padding:0;margin-left:.5rem">Çıkış</button>
        </form>
      <?php else: ?>
        · <a href="/login">Giriş</a> · <a href="/register">Kayıt</a> · <a href="/password/forgot">Şifremi unuttum</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container">
    <?= component('flash') ?>  <!-- 🔔 Flash mesajlar burada görünecek -->
    <?= isset($content) ? $content : '' ?>
  </main>

  <footer class="container small muted">
    <hr>
    <p>ZCNCore • v<?= e(app()->version()) ?> • <?= number_format((microtime(true)-app()->startedAt()), 3) ?>s</p>
  </footer>
  <script src="<?= e(asset('/themes/default/assets/js/app.js')) ?>"></script>
</body>
</html>