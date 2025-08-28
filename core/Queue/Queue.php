<?php
namespace Core\Queue;

use Core\Config;
use Core\Application;
use Core\Logger;

final class Queue
{
    /** Kuyruğa iş ekle (class adı + payload) */
    public static function push(string $class, array $data = []): string
    {
        $cfg  = Config::get('queue');
        $dir  = $cfg['path'] ?? (Application::get()->basePath('storage/queue'));
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $job = [
            'id'        => self::uuid(),
            'class'     => $class,    // ör: App\Jobs\WelcomeMailJob
            'data'      => $data,     // iş verisi
            'attempts'  => 0,
            'max'       => (int)($cfg['max_attempts'] ?? 3),
            'createdAt' => time(),
        ];

        $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $job['id'] . '.json';
        $ok   = @file_put_contents($path, json_encode($job, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
        if ($ok === false) {
            throw new \RuntimeException("Queue write failed: {$path}");
        }
        return $job['id'];
    }

    /** Worker: sonsuz döngüde iş tüketir */
    public static function work(): void
    {
        $cfg      = Config::get('queue');
        $dir      = $cfg['path'] ?? (Application::get()->basePath('storage/queue'));
        $failed   = $cfg['failed_path'] ?? (Application::get()->basePath('storage/queue_failed'));
        $sleep    = (int)($cfg['sleep'] ?? 2);

        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        if (!is_dir($failed)) @mkdir($failed, 0777, true);

        // Worker banner
        echo "[queue] working on {$dir}\n";
        while (true) {
            $file = self::nextJobFile($dir);
            if (!$file) {
                // iş yok → biraz uyu
                sleep($sleep);
                continue;
            }

            $job = json_decode((string)@file_get_contents($file), true);
            if (!is_array($job)) { @unlink($file); continue; }

            $job['attempts'] = (int)($job['attempts'] ?? 0) + 1;

            $ok = self::runJob($job);
            if ($ok) {
                @unlink($file);
                echo "[queue] done {$job['id']} ({$job['class']})\n";
            } else {
                if ($job['attempts'] >= (int)$job['max']) {
                    // Failed queue
                    $dest = rtrim($failed, '/\\') . DIRECTORY_SEPARATOR . basename($file);
                    @rename($file, $dest);
                    echo "[queue] failed {$job['id']} moved to failed\n";
                } else {
                    // attempts +1 ile geri koy
                    @file_put_contents($file, json_encode($job, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
                    echo "[queue] retry scheduled {$job['id']} (attempt {$job['attempts']})\n";
                    sleep(1);
                }
            }
        }
    }

    /** Bir job dosyası seç (en eskiyi al) */
    private static function nextJobFile(string $dir): ?string
    {
        $files = glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*.json');
        if (!$files) return null;
        sort($files, SORT_NATURAL|SORT_FLAG_CASE);
        return $files[0] ?? null;
    }

    /** Job’ı çalıştır */
    private static function runJob(array $job): bool
    {
        $class = (string)($job['class'] ?? '');
        $data  = (array)($job['data'] ?? []);

        if (!$class) return true; // bozuksa at

        // Sınıf dosyasını yüklemeyi dene
        self::requireClass($class);

        if (!class_exists($class)) {
            Logger::error("[queue] class not found: {$class}");
            return false;
        }

        try {
            $instance = new $class();
            if (!method_exists($instance, 'handle')) {
                Logger::error("[queue] handle() missing: {$class}");
                return false;
            }
            $res = $instance->handle($data);
            return $res !== false; // true/void → success, false → fail
        } catch (\Throwable $e) {
            Logger::error("[queue] exception in {$class}: " . $e->getMessage());
            return false;
        }
    }

    /** Sınıfı namespace → dosya yoluna çevirip include et */
    private static function requireClass(string $class): void
    {
        $base = Application::get()->basePath();
        $rel  = str_replace('\\', '/', $class) . '.php';

        // App/, Modules/, Core/ sırasıyla dene
        $paths = [
            $base . '/app/' . $rel,                // App\Jobs\X → app/Jobs/X.php
            $base . '/modules/' . $rel,            // Modules\Blog\Jobs\X → modules/Blog/Jobs/X.php
            $base . '/core/' . $rel,               // Core\Something → core/Something.php
        ];
        foreach ($paths as $p) {
            if (is_file($p)) { require_once $p; return; }
        }

        // Özel çözümler: App\Jobs\X
        if (str_starts_with($class, 'App\\')) {
            $p = $base . '/' . str_replace('App\\', 'app/', $class) . '.php';
            $p = str_replace('\\', '/', $p);
            if (is_file($p)) { require_once $p; return; }
        }
        if (str_starts_with($class, 'Modules\\')) {
            $p = $base . '/' . str_replace('Modules\\', 'modules/', $class) . '.php';
            $p = str_replace('\\', '/', $p);
            if (is_file($p)) { require_once $p; return; }
        }
    }

    private static function uuid(): string
    {
        // Basit uniq id (time + random)
        return dechex(time()) . '-' . bin2hex(random_bytes(6));
    }

/** Failed klasör yolunu getir */
    public static function failedPath(): string
    {
        $cfg    = \Core\Config::get('queue');
        $failed = $cfg['failed_path'] ?? (\Core\Application::get()->basePath('storage/queue_failed'));
        if (!is_dir($failed)) @mkdir($failed, 0777, true);
        return rtrim($failed, '/\\');
    }

    /** Başarısız işleri (ID, class, attempts, createdAt) listele */
    public static function failedJobs(): array
    {
        $dir = self::failedPath();
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.json') ?: [];
        sort($files, SORT_NATURAL|SORT_FLAG_CASE);

        $out = [];
        foreach ($files as $f) {
            $j = json_decode((string)@file_get_contents($f), true);
            if (!is_array($j)) continue;
            $out[] = [
                'id'        => (string)($j['id'] ?? basename($f, '.json')),
                'class'     => (string)($j['class'] ?? ''),
                'attempts'  => (int)($j['attempts'] ?? 0),
                'max'       => (int)($j['max'] ?? 0),
                'createdAt' => (int)($j['createdAt'] ?? 0),
                'path'      => $f,
            ];
        }
        return $out;
    }

    /** Failed → retry (tekrar kuyruğa almak) */
    public static function retryFailed(string $id): bool
    {
        $dir = self::failedPath();
        $src = $dir . DIRECTORY_SEPARATOR . basename($id) . '.json';
        if (!is_file($src)) return false;

        $job = json_decode((string)@file_get_contents($src), true);
        if (!is_array($job)) return false;

        // attempts sıfırla ve kuyruğa geri koy
        $job['attempts'] = 0;
        $cfg  = \Core\Config::get('queue');
        $qdir = $cfg['path'] ?? (\Core\Application::get()->basePath('storage/queue'));
        if (!is_dir($qdir)) @mkdir($qdir, 0777, true);
        $dest = rtrim($qdir, '/\\') . DIRECTORY_SEPARATOR . $job['id'] . '.json';
        $ok   = @file_put_contents($dest, json_encode($job, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
        if ($ok === false) return false;

        @unlink($src);
        return true;
    }

    /** Failed → kalıcı silme */
    public static function deleteFailed(string $id): bool
    {
        $dir = self::failedPath();
        $src = $dir . DIRECTORY_SEPARATOR . basename($id) . '.json';
        return @unlink($src);
    }    
}
