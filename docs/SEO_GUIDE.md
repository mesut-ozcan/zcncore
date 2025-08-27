# SEO Guide

## Core
- Canonical host & trailing-slash normalization (Kernel middleware).
- Head manager: set `title/description/canonical/robots`, render via `<?= head()->render() ?>`.
- `/sitemap.xml` & `/robots.txt` are served by registries that modules can populate.

## Module (SEO)
- Optional admin UI for meta tags, redirects manager, JSON-LD presets.
- Add URLs to `SitemapRegistry::add([...])`, lines to `RobotsRegistry::add("Disallow: /x")`.

## Theme
- Controls `<title>` formatting, OG/Twitter tags template, layout markup.

Checklist:
- Search results pages → `noindex,follow`.
- Canonical is self-referential by default.
- Split sitemaps for >50k URLs; cache results.
