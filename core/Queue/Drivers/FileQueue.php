<?php
namespace Core\Queue\Drivers;

final class FileQueue
{
    private string $path;
    private string $failedPath;
    private int $maxAttempt;

    public function __construct(string $path, string $failedPath, int $maxAttempt)
    {
        $this->path = rtrim($path, "/\\");
        $this->failedPath = rtrim($failedPath, "/\\");
        $this->maxAttempt = $maxAttempt;

        if (!is_dir($this->path))       @mkdir($this->path, 0777, true);
        if (!is_dir($this->failedPath)) @mkdir($this->failedPath, 0777, true);
    }

    /** @return string job id */
    public function push(string $jobClass, array $payload = []): string
    {
        $id = bin2hex(random_bytes(16));
        $item = [
            'id'       => $id,
            'class'    => $jobClass,
            'payload'  => $payload,
            'attempt'  => 0,
            'enqueued' => time(),
            'reserved' => 0,
        ];
        $file = $this->path . DIRECTORY_SEPARATOR . $id . '.json';
        $ok = @file_put_contents($file, json_encode($item, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
        if ($ok === false) {
            throw new \RuntimeException("Queue write failed: {$file}");
        }
        return $id;
    }

    /** @return array{id:string,class:string,payload:array,attempt:int,reserved:int,enqueued:int}|null */
    public function pop(): ?array
    {
        $files = glob($this->path . DIRECTORY_SEPARATOR . '*.json');
        if (!$files) return null;

        sort($files, SORT_STRING); // FIFO benzeri

        foreach ($files as $f) {
            $data = json_decode((string)@file_get_contents($f), true);
            if (!is_array($data)) continue;

            // re-reserve koruması: aynı anda iki worker kapmasın
            $data['reserved'] = time();
            $data['attempt']  = (int)($data['attempt'] ?? 0) + 1;

            // Yüksek attempt kontrolü (fail'e taşı)
            if ($data['attempt'] > $this->maxAttempt) {
                $this->moveToFailed($f, $data, 'max_attempt_exceeded');
                @unlink($f);
                continue;
            }

            // atomik yaz
            $tmp = $f . '.lock';
            if (@file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) !== false) {
                // lock dosyasını asıl dosyaya rename (Windows uyumlu atomik olmasa da yeterli)
                @rename($tmp, $f);
                return $data;
            }
        }
        return null;
    }

    /** başarıyla tamamlandı → kuyruq dosyasını sil */
    public function ack(array $job): void
    {
        $file = $this->path . DIRECTORY_SEPARATOR . $job['id'] . '.json';
        @unlink($file);
    }

    /** başarısız → attempt sınırındaysa failed klasörüne taşı, değilse geri bırak (attempt artmış hâli zaten dosyada) */
    public function fail(array $job): void
    {
        $file = $this->path . DIRECTORY_SEPARATOR . $job['id'] . '.json';
        if (!is_file($file)) return;

        if (($job['attempt'] ?? 1) >= $this->maxAttempt) {
            $this->moveToFailed($file, $job, 'attempt_reached');
            @unlink($file);
            return;
        }

        // tekrar denenecek → reserved sıfırla, dosya içerik güncel zaten
        $job['reserved'] = 0;
        @file_put_contents($file, json_encode($job, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    private function moveToFailed(string $srcFile, array $job, string $reason): void
    {
        $job['failed_at'] = time();
        $job['fail_reason'] = $reason;
        $dst = $this->failedPath . DIRECTORY_SEPARATOR . $job['id'] . '.json';
        @file_put_contents($dst, json_encode($job, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    }
}