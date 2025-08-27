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
