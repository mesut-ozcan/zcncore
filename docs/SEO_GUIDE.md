# SEO Guide

## Core
- Canonical host & trailing-slash normalization (Kernel middleware).
- Head manager: set `title/description/canonical/robots`, render via `<?= head()->render() ?>`.
- NEW: `Head::addProperty('og:title','...')` ve `Head::addProperty('twitter:card','summary')` gibi OG/Twitter desteği.
- `/sitemap.xml` & `/robots.txt` registries.

## Module (SEO)
- Modüller sitemap/robots verisi ekleyebilir:
  ```php
  \Core\SEO\SitemapRegistry::add(['loc'=>config('app.url').'/products', 'changefreq'=>'weekly']);
  \Core\SEO\RobotsRegistry::add('Disallow: /admin');
