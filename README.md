# ZCNCore

Minimal, **Saf PHP** tabanlı çekirdek — module + theme yapısıyla çok projede tekrar kullanılabilir.
Bu repo **MVP** bir iskelet sağlar: Router, Request/Response, View resolver, Config/Env, CSRF, Events,
Logger, Cache, Head (SEO), Sitemap/Robots hook'ları, basit Module/Theme sözleşmeleri.

## Kurulum

```bash
composer install       # sadece autoload için (bağımlılık yok)
cp .env.example .env
php -S 127.0.0.1:8000 -t public
# tarayıcı: http://127.0.0.1:8000/
