<?php
namespace Core\Notification;

use Core\Logger;

final class Notifier
{
    /**
     * Basit mail gönderici. mail() yoksa log’a yazar.
     */
    public static function send(string $to, string $subject, string $message, array $headers = []): bool
    {
        $headersStr = self::headersToString($headers);

        // mail() fonksiyonu yoksa veya başarısızsa log’a düş.
        if (!function_exists('mail')) {
            Logger::info("[MAIL-FAKE] to={$to} subject={$subject}\n{$message}");
            return true;
        }

        $ok = @mail($to, $subject, $message, $headersStr);
        if (!$ok) {
            Logger::error("[MAIL-FAIL] to={$to} subject={$subject}\n{$message}");
        }
        return $ok;
    }

    private static function headersToString(array $headers): string
    {
        if (empty($headers)) {
            // varsayılan text/plain + UTF-8
            return "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        }
        $lines = [];
        foreach ($headers as $k => $v) {
            $lines[] = "{$k}: {$v}";
        }
        return implode("\r\n", $lines);
    }
}
