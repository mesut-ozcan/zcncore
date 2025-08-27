<?php
namespace App\Middleware;

use Core\Request;
use Core\Response;

class RateLimitMiddleware
{
    private int $maxPerMinute;
    private string $storeDir;

    public function __construct(int $maxPerMinute = 30)
    {
        $this->maxPerMinute = $maxPerMinute;
        $this->storeDir = \base_path('storage/cache/ratelimit');
        if (!is_dir($this->storeDir)) @mkdir($this->storeDir, 0777, true);
    }

    public function __invoke(Request $req, callable $next)
    {
        $ip   = $req->server['REMOTE_ADDR'] ?? '0.0.0.0';
        $path = $req->path();
        $uid  = $_SESSION['user']['id'] ?? null;

        // Kullanıcı girişi varsa anahtarı user-id bazlı yap, yoksa IP
        $key = $uid ? "u:$uid:$path" : "ip:$ip:$path";
        $now = time();
        $bucket = (int)floor($now / 60); // dakika kovası
        $file = $this->storeDir . '/' . md5($key) . '.json';

        $data = ['bucket'=>$bucket, 'count'=>0];
        if (is_file($file)) {
            $data = json_decode((string)file_get_contents($file), true) ?: $data;
            if (($data['bucket'] ?? 0) !== $bucket) {
                $data = ['bucket'=>$bucket, 'count'=>0];
            }
        }

        $data['count']++;
        @file_put_contents($file, json_encode($data));

        if ($data['count'] > $this->maxPerMinute) {
            // JSON isteyenlere JSON
            if ($req->wantsJson()) {
                return Response::json([
                    'ok'=>false,
                    'error'=>'Too Many Requests',
                    'retry_after'=> 60 - ($now % 60)
                ], 429, ['Retry-After' => (string)(60 - ($now % 60))]);
            }
            return new Response('<h1>429 Too Many Requests</h1>', 429, ['Retry-After' => (string)(60 - ($now % 60))]);
        }

        return $next($req);
    }
}
