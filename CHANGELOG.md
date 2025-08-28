## 3.3.2
- Queue: `pushDelayed()` ve `scheduleAt()` ile zamanlama; worker artık availableAt<=now olan işleri tüketir
- Admin: Failed Job detay sayfası (ham JSON dahil)
- CLI: `queue:push <Class> <json> [--delay=sec|--at=timestamp]`

## 3.3.1
- Admin: Failed Jobs ekranı (liste/Retry/Delete)
- Queue: failedJobs(), retryFailed(), deleteFailed() yardımcıları
- CLI: make:job komutu

## 3.2.1
- Admin arayüzü iyileştirmeleri: kartlar, butonlar, log görünümü ve satır filtresi
- Tema CSS: kart, buton, tablo, log ve alert stilleri
- Küçük UX dokunuşları (flash auto-hide)

## 3.2.0
- Admin panel (MVP): Dashboard (version/uptime/php/cache), Log viewer (tail + download), Cache clear (POST, CSRF)
- Rotalar admin middleware ile korundu
- Tema menüsüne Admin bağlantıları

## 3.1.0
- PHPUnit smoke testleri (Router/Response/Helpers)
- GitHub Actions CI (PHP 8.2/8.3)
- Dev-only composer (phpunit)

## 3.0.0
- CLI Installer: `php cli/zcn install` (.env oluşturur, APP_KEY üretir, storage dizinlerini hazırlar, migrate çalıştırır)
- Scaffold komutları:
  - `make:module <Name>` (routes, controller, view, module.json)
  - `make:theme <name>` (views + public/assets iskeleti)
  - `make:controller <Name> [--module=Blog]` (app/ veya modules/<Modül>/ içine)
- Küçük iyileştirmeler: Windows/WAMP uyumlu yol yazımı, güvenli dosya oluşturma

## v2.9.0
- Session: file + database driver (sliding expiration)
- RateLimiter v2: config tabanlı route/method limitleri
- CSRF: /csrf/refresh ucu + JS helper ile token yenileme
- Error pages: 404 / 500
- Request logging middleware
- Assets: tema asset’leri public/ altına taşındı (performans ve doğru servis için)
- mix() helper: manifest (Vite/Laravel Mix) → fingerprint’li yollar; yoksa asset() fallback
- CLI: config:cache / config:clear; migrate/tags iyileştirmeleri

## 2.8.0
- Error pages: `themes/default/views/errors/404.php`, `500.php` (HTML + JSON fallback)
- Middleware: `RequestLogger` her isteği loglar (ip, method, path, duration, status)

## 2.7.0
- Logger: Günlük dosyaları günlük bazında (`app-{Y-m-d}.log`), otomatik eski log temizliği (`retention_days`)
- Status: Sağlık kontrolü (DB/Cache/Mail/Queue/Env) — `/status` HTML & JSON
- Assets: `asset()` helper ile fingerprint (`?v=mtime`) ve absolute URL desteği

## 2.6.0
- Mail: `mail_send()` helper (driver: `mail` veya basit `smtp` AUTH LOGIN + TLS/SSL)
- Queue: `Queue::dispatch()` (sync|file), `queue:work` worker ile dosya tabanlı kuyruğu tüket
- CLI: `config:cache`, `config:clear` komutları (Config dosyası önbelleği)

## 2.5.0
- Auth: Güvenli **remember me** (imzalı + şifreli cookie), `Core\Auth\Remember::issue/forget/userIdFromCookie`
- RateLimit: IP + kullanıcı ID bazlı anahtar; 429 JSON/HTML yanıtları
- View Cache: `view_cached()` helper; `storage/cache/views` altında HTML cache

## 2.4.0
- Session: Flash & Old Input altyapısı (`flash()`, `old()`, `errors()`), `Session::boot()`
- Router: FormRequest başarısızlığında HTML istekleri için **geri yönlendirme** + old & errors flash; JSON istekleri için 422 JSON
- CSRF: JSON isteklerinde standart hata gövdesi (HTTP 419), HTML’de flash+redirect
- Theme: `flash` component; layout’a ekleyerek mesajları göster

## 2.3.0
- Router: Controller action param’larında **FormRequest** otomatik çözümleme + otomatik validation; hata → 422 (JSON isteyenlere JSON)
- Router: Middleware alias’ı **class-string** kabul eder ve gerektiğinde instantiate eder
- View: `pagination` component (themes/default/components/pagination.php) — `paginate()` çıktısını render eder

## 2.2.0
- Router: Group içinde route **name prefix** desteği (`group('/admin', ..., ..., 'admin.')`)
- Validation: **FormRequest** tabanı (rules/authorize/messages; `$request->validated()`)
- Support: **Paginator** helper (`paginate()`), HTML linkleri ve offset/limit hesapları

## 2.1.0
- Router: Route groups & prefix (`router->group('/admin', ['auth','admin'], fn($r)=>...)`)
- Auth: Mini Gate/Policy (`Core\Auth\Gate`) ile yetenek tanımları (`users.manage`, `account.update` örnekleri)
- View: Component renderer (`component('name', [...])`; bak: themes/default/components / app/Views/components)
- Controller: `authorizeGate($ability, $args)` eklendi (rol temelli `authorize()` BC korunur)

## 2.0.0
- Router: Named routes + URL generator (`route('name', params, absolute=false)`), `getNamed/postNamed/...`
- Helpers: `route()` helper (Router::urlFor sarmalayıcı)
- Controller: `Core\Controller` taban sınıfı (`view()`, `json()`, `redirect()`, `authorize()`)
- Config: Per-env override (`app/Config/*.{APP_ENV}.php`), `config:cache` ile birlikte çalışır

## 1.9.0
- Response: `file()` ile inline dosya sunumu; `ETag` ve `Last-Modified` başlıkları; `If-None-Match/If-Modified-Since` → 304
- Upload: Görsellere max genişlik/yükseklik doğrulaması (config `upload.images`)
- Request: `wantsJson()` / `expectsJson()` helper’ları
- Kernel: Exception handler JSON algılaması `wantsJson()` ile

## 1.8.0
- Upload güvenliği: mime/uzantı doğrulama, güvenli dosya adı, `storage/uploads/YYYY/MM` altına taşıma
- Cookie helpers: `cookie_set()`, `cookie_get()`; Response: `withCookie()` ile çoklu `Set-Cookie`
- Dev server: `php cli/zcn serve [host:port]`
- Response: header gönderimi çoklu satır desteği (özg. `Set-Cookie`)

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
