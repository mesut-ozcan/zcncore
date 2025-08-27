<?php
namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Cache;

/**
 * Basit IP+path bazlı throttle.
 * Login (POST /login):    5 deneme / 60s
 * Forgot (POST /password/forgot): 3 deneme / 600s
 * Reset (POST /password/reset/{token}): 5 deneme / 600s
 */
class RateLimitMiddleware
{
    public function __invoke(Request $req, callable $next)
    {
        $method = strtoupper($req->method);
        $path   = $req->path();
        if ($method !== 'POST') {
            return $next($req);
        }

        $limit = 5; $decay = 60; // default
        if (preg_match('#^/password/forgot$#', $path)) { $limit = 3; $decay = 600; }
        if (preg_match('#^/password/reset/#', $path))  { $limit = 5; $decay = 600; }

        $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key  = 'ratelimit:' . sha1($ip . '|' . $method . '|' . $path);

        $hit = Cache::get($key, ['count'=>0,'reset'=>time()+$decay]);
        if (!is_array($hit)) $hit = ['count'=>0,'reset'=>time()+$decay];

        if ($hit['count'] >= $limit) {
            $retry = max(1, $hit['reset'] - time());
            $body  = "<h1>429 Too Many Requests</h1><p>Lütfen {$retry} saniye sonra tekrar deneyin.</p>";
            return new Response($body, 429, ['Retry-After' => (string)$retry]);
        }

        $hit['count']++;
        if ($hit['reset'] < time()) {
            $hit = ['count'=>1, 'reset'=>time()+$decay];
        }
        Cache::set($key, $hit, $decay);

        return $next($req);
    }
}
