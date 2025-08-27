# Architecture (MVP)

- **Core**: Router, Request/Response, View resolver, Config/Env, Csrf, Events, Logger, Cache, Head (SEO), SEO registries, Kernel.
- **App**: Default controllers/middleware/views. Project-level overrides.
- **Modules**: Self-contained features. Each has `module.json`, `routes.php`, optional migrations/views.
- **Themes**: Only presentation. View override order: `theme > module > app`.
- **SEO**: Canonical host/slash normalizer, Head manager, `/sitemap.xml` and `/robots.txt` endpoints via registries.
- **CLI**: `cache:clear`, `module:list`, `make:module`.

Public API surface (stable):
- helpers: `config(), env(), e(), csrf_field(), asset(), route(), view(), base_path(), head()`
- app: `app()->version()`, `app()->make(id)`
- http: via Router patterns `/path/{param}`

Rules:
- 100-line & 3-project rule for adding new core features.
