<?php
namespace Core\Queue;

use Core\Config;

final class Queue
{
    /** push: job class + payload döner: string id */
    public static function push(string $jobClass, array $payload = []): string
    {
        $driver = self::driver();
        return $driver->push($jobClass, $payload);
    }

    /** worker: sonsuz döngü (config'e göre) işleri çeker ve çalıştırır */
    public static function work(): void
    {
        $driver = self::driver();
        $sleep  = (int)Config::get('queue.worker.sleep', 2);
        $stopWhenEmpty = (bool)Config::get('queue.worker.stop_when_empty', false);

        while (true) {
            $job = $driver->pop();
            if ($job === null) {
                if ($stopWhenEmpty) {
                    echo "[queue] empty, stop.\n";
                    return;
                }
                sleep($sleep);
                continue;
            }

            $ok = false;
            try {
                $ok = self::runJob($job['class'], $job['payload']);
            } catch (\Throwable $e) {
                $ok = false;
                echo "[queue] job threw: " . $e->getMessage() . "\n";
            }

            if ($ok) {
                $driver->ack($job);
                echo "[queue] done id={$job['id']}\n";
            } else {
                $driver->fail($job);
                echo "[queue] failed id={$job['id']} (attempt={$job['attempt']})\n";
            }
        }
    }

    /** job sınıfını çalıştırır: handle(array $data): bool */
    private static function runJob(string $jobClass, array $payload): bool
    {
        if (!class_exists($jobClass)) {
            throw new \RuntimeException("Job class not found: {$jobClass}");
        }
        $job = new $jobClass();
        if (!method_exists($job, 'handle')) {
            throw new \RuntimeException("Job class {$jobClass} must have handle(array \$data): bool");
        }
        $res = $job->handle($payload);
        return (bool)$res;
    }

    /** @return \Core\Queue\Drivers\FileQueue */
    private static function driver()
    {
        $driver = Config::get('queue.driver', 'file');
        switch ($driver) {
            case 'file':
            default:
                return new Drivers\FileQueue(
                    (string)Config::get('queue.file.path'),
                    (string)Config::get('queue.file.failed_path'),
                    (int)Config::get('queue.worker.max_attempt', 3)
                );
        }
    }
}