<?php
namespace Core\Queue;

use Core\Config;

interface JobInterface {
    /** @param array $payload */
    public function handle(array $payload = []): void;
}

final class Queue
{
    public static function dispatch(string $jobClass, array $payload = []): void
    {
        $driver = (string)Config::get('queue.driver', 'sync');
        if ($driver === 'sync') {
            self::runNow($jobClass, $payload);
            return;
        }

        // file driver
        $pathRel = (string)Config::get('queue.file.path', 'storage/queue');
        $dir = \base_path($pathRel);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $item = [
            'id' => bin2hex(random_bytes(8)),
            'class' => $jobClass,
            'payload' => $payload,
            'ts' => time(),
        ];
        $file = $dir . '/' . $item['id'] . '.json';
        @file_put_contents($file, json_encode($item));
    }

    public static function work(int $max = 0, int $sleep = 2): void
    {
        $pathRel = (string)Config::get('queue.file.path', 'storage/queue');
        $dir = \base_path($pathRel);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $processed = 0;
        while (true) {
            $files = glob($dir . '/*.json') ?: [];
            if (!$files) {
                if ($max > 0 && $processed >= $max) break;
                sleep($sleep);
                continue;
            }

            foreach ($files as $f) {
                $json = json_decode((string)file_get_contents($f), true) ?: null;
                @unlink($f); // optimistic delete
                if (!$json) continue;

                $cls = (string)($json['class'] ?? '');
                $payload = (array)($json['payload'] ?? []);

                self::runNow($cls, $payload);
                $processed++;
                if ($max > 0 && $processed >= $max) break 2;
            }
        }
    }

    private static function runNow(string $cls, array $payload): void
    {
        if (!class_exists($cls)) {
            throw new \RuntimeException("Queue job class not found: $cls");
        }
        $job = new $cls();
        if (!($job instanceof JobInterface)) {
            // Esneklik: handle metodu olan düz sınıflara da izin ver
            if (method_exists($job, 'handle')) {
                $job->handle($payload);
                return;
            }
            throw new \RuntimeException("Job must implement JobInterface or have handle()");
        }
        $job->handle($payload);
    }
}
