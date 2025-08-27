## 1.7.1
- Response: `private ?callable $streamer` → property type kaldırıldı, docblock ile işaretlendi (PHP uyumluluk fix).

## 1.7.0
- Response helpers: `json()`, `download()`, `stream()`, `noCache()`
- Exception handler: `Accept: application/json` isteklerinde JSON hata gövdesi (debug modda detaylı)
- Router: middleware alias desteği (`router->alias('csrf', ...)`; rotalarda `['csrf','auth']` kullanımı)

## 1.6.1
- Helpers: `view()` helper geri eklendi (theme > module > app çözümleme). 1.6.0 sonrası undefined function hatası düzeltildi.

## 1.6.0
- Validation: `required`, `email`, `string`, `min`, `max`, `confirmed` kuralları; `Request::validate()`
- Request helpers: `Request::current()`, `input()`, `all()`
- Rate-limit: dosya tabanlı kalıcı `RateLimiter` + middleware entegrasyonu
- CLI: `php cli/zcn rate:clear`

## 1.5.1
- Helpers: `head()` artık gerçek `\Core\Head` döndürüyor (Intelephense uyumu).

## 1.5.0
- Config cache: `php cli/zcn config:cache` / `config:clear`
- Modül enable/disable: module.json `enabled` + `app/Config/modules.php` override
- CSRF: SameSite=Lax cookie (XSRF-TOKEN) + header kontrolü (X-CSRF-TOKEN / X-XSRF-TOKEN)
- Helpers: `csrf_meta()` eklendi

## 1.4.0
- Head: OG/Twitter meta desteği (`addProperty`)
- Users: Rate-limit middleware (login/forgot/reset POST)
- Logs modülü: Admin log viewer + download
- Tema: OG/Twitter varsayılanları, admin menüde Logs linki

## 1.3.0
- Users modülü DB’ye taşındı (PDO)
- Şifre sıfırlama (forgot/reset) akışı
- `users` ve `password_resets` tabloları migration

## 1.2.1
- Migration runner: transaction guard (DDL implicit commit fix)
- Pages & demo migrations

## 1.2.0
- PDO Database provider + migration runner (migrate/rollback/status/make:migration)

## 1.1.0
- /status sağlık ucu (dev-only)
- RedirectRegistry + config tabanlı kurallar
- Router global middleware fix
- Sitemap örnek kayıtları

## 1.0.0 (MVP)
- Router, Request/Response, View resolver (theme>module>app), Config/Env,
  CSRF, Events, Logger, Cache, Head (SEO), Sitemap/Robots uçları, Pages modülü,
  default tema, CLI iskeleti, dokümantasyon.
