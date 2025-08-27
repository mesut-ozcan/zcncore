<?php
namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Support\RateLimiter;

class RateLimitMiddleware
{
    public function __invoke(Request $req, callable $next)
    {
        if (strtoupper($req->method) !== 'POST') {
            return $next($req);
        }

        $path = $req->path();
        $cfg  = require base_path('app/Config/ratelimit.php');

        $limit = $cfg['default']['limit'];
        $decay = $cfg['default']['decay'];

        foreach ($cfg['paths'] as $pattern => $rule) {
            if (preg_match($pattern, $path)) {
                $limit = $rule['limit'];
                $decay = $rule['decay'];
                break;
            }
        }

        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = $ip . '|' . $req->method . '|' . $path;

        $store = new RateLimiter(base_path('storage/ratelimit'));
        $state = $store->hit($key, $limit, $decay);

        if ($state['count'] > $limit) {
            $retry = max(1, $state['reset'] - time());
            return new Response(
                "<h1>429 Too Many Requests</h1><p>{$retry} saniye bekleyin.</p>",
                429,
                ['Retry-After' => (string)$retry]
            );
        }

        return $next($req);
    }
}
