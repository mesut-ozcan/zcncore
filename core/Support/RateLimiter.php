<?php
namespace Core\Support;

final class RateLimiter
{
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = rtrim($dir, '/');
        if (!is_dir($this->dir)) @mkdir($this->dir, 0777, true);
    }

    private function path(string $key): string
    {
        return $this->dir . '/' . sha1($key) . '.json';
    }

    /** @return array{count:int, reset:int} */
    public function hit(string $key, int $limit, int $decay): array
    {
        $file = $this->path($key);
        $now  = time();
        $data = ['count'=>0, 'reset'=>$now + $decay];

        if (is_file($file)) {
            $raw = json_decode((string)file_get_contents($file), true);
            if (is_array($raw)) $data = array_merge($data, $raw);
        }

        if ($data['reset'] <= $now) {
            $data = ['count'=>0, 'reset'=>$now + $decay];
        }

        $data['count']++;

        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        return $data;
    }

    public function clearAll(): void
    {
        foreach (glob($this->dir.'/*.json') ?: [] as $f) { @unlink($f); }
    }
}
