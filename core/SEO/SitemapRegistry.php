<?php
namespace Core\SEO;

final class SitemapRegistry
{
    private static array $urls = []; // ['loc'=>..., 'lastmod'=>..., 'changefreq'=>..., 'priority'=>...]

    public static function add(array $url): void
    {
        self::$urls[] = $url;
    }

    public static function render(): string
    {
        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach (self::$urls as $u) {
            $xml[] = '<url>';
            $xml[] = '<loc>' . htmlspecialchars($u['loc'] ?? '', ENT_QUOTES, 'UTF-8') . '</loc>';
            if (!empty($u['lastmod'])) $xml[] = '<lastmod>' . htmlspecialchars($u['lastmod'], ENT_QUOTES, 'UTF-8') . '</lastmod>';
            if (!empty($u['changefreq'])) $xml[] = '<changefreq>' . htmlspecialchars($u['changefreq'], ENT_QUOTES, 'UTF-8') . '</changefreq>';
            if (!empty($u['priority'])) $xml[] = '<priority>' . htmlspecialchars($u['priority'], ENT_QUOTES, 'UTF-8') . '</priority>';
            $xml[] = '</url>';
        }
        $xml[] = '</urlset>';
        return implode("\n", $xml);
    }
}
