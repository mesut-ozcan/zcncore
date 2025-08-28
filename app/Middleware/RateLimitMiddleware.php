<?php
namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Config;

class RateLimitMiddleware
{
    public function __invoke(Request $req, callable $next): Response
    {
        $cfg = Config::get('rate_limit', []);
        $default = $cfg['default'] ?? ['max'=>60,'per'=>60];

        $limit = $default; // varsayılan
        $path  = $req->server['REQUEST_URI']  ?? '/';
        $meth  = $req->server['REQUEST_METHOD'] ?? 'GET';

        foreach (($cfg['routes'] ?? []) as $rule) {
            $okMethod = empty($rule['methods']) || in_array($meth, (array)$rule['methods'], true);
            $okMatch  = !empty($rule['match']) && @preg_match($rule['match'], $path);
            if ($okMethod && $okMatch) {
                $limit = ['max'=>(int)$rule['max'], 'per'=>(int)$rule['per']];
                break;
            }
        }

        $ip = $req->server['HTTP_X_FORWARDED_FOR'] ?? ($req->server['REMOTE_ADDR'] ?? '0.0.0.0');
        $key = sha1($ip . '|' . $meth . '|' . $path);

        $now = time();
        $bucket = $_SESSION['_rate'][$key] ?? ['count'=>0,'reset'=>$now + $limit['per']];
        if ($bucket['reset'] < $now) {
            $bucket = ['count'=>0,'reset'=>$now + $limit['per']];
        }
        $bucket['count']++;

        $_SESSION['_rate'][$key] = $bucket;

        if ($bucket['count'] > $limit['max']) {
            $headers = [
                'Retry-After' => (string)max(1, $bucket['reset'] - $now),
                'X-RateLimit-Limit' => (string)$limit['max'],
                'X-RateLimit-Remaining' => '0',
            ];
            if ($req->wantsJson()) {
                return Response::json(['ok'=>false,'error'=>'Too Many Requests'], 429, $headers);
            }
            return new Response('<h1>429 Too Many Requests</h1>', 429, $headers);
        }

        $resp = $next($req);
        // bilgi amaçlı header
        $remaining = max(0, $limit['max'] - $bucket['count']);
        return $resp->withHeader('X-RateLimit-Limit', (string)$limit['max'])
                    ->withHeader('X-RateLimit-Remaining', (string)$remaining);
    }
}
