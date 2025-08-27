<?php
namespace Core\Support;

use Core\Config;
use Core\Mail\Mailer;

final class Health
{
    /** DB bağlantısını dener. */
    public static function db(): array
    {
        try {
            if (class_exists(\Core\Database\Connection::class)) {
                $pdo = \Core\Database\Connection::getInstance()->pdo();
                $pdo->query('SELECT 1');
                return ['ok'=>true];
            }
            return ['ok'=>false,'error'=>'Connection class missing'];
        } catch (\Throwable $e) {
            return ['ok'=>false,'error'=>$e->getMessage()];
        }
    }

    /** Cache dizinine yaz/oku testi */
    public static function cache(): array
    {
        try {
            $dir = \base_path('storage/cache/health');
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $file = $dir.'/ping.txt';
            $token = bin2hex(random_bytes(8));
            file_put_contents($file, $token);
            $ok = (trim((string)file_get_contents($file)) === $token);
            if (!$ok) return ['ok'=>false,'error'=>'mismatch'];
            @unlink($file);
            return ['ok'=>true];
        } catch (\Throwable $e) {
            return ['ok'=>false,'error'=>$e->getMessage()];
        }
    }

    /** SMTP’e kısa el sıkışma (driver smtp ise); 'mail' driverında sadece konfig kontrolü. */
    public static function mail(): array
    {
        try {
            $driver = (string)Config::get('mail.driver','mail');
            if ($driver !== 'smtp') {
                return ['ok'=>true,'note'=>'driver=mail'];
            }
            $smtp = Config::get('mail.smtp',[]);
            $host = (string)($smtp['host'] ?? '127.0.0.1');
            $port = (int)($smtp['port'] ?? 25);
            $remote = (($smtp['secure'] ?? null) === 'ssl' ? 'ssl://' : '') . $host;
            $fp = @fsockopen($remote,$port,$errno,$errstr, (int)($smtp['timeout'] ?? 5));
            if (!$fp) return ['ok'=>false,'error'=>"$errno $errstr"];
            $greet = fgets($fp, 512);
            fclose($fp);
            if (strpos((string)$greet,'220') === 0) return ['ok'=>true];
            return ['ok'=>false,'error'=>'bad greeting'];
        } catch (\Throwable $e) {
            return ['ok'=>false,'error'=>$e->getMessage()];
        }
    }

    /** Queue dizini yazılabilir mi (file driver için) */
    public static function queue(): array
    {
        try {
            $driver = (string)Config::get('queue.driver','sync');
            if ($driver !== 'file') return ['ok'=>true,'note'=>'driver='.$driver];
            $rel = (string)Config::get('queue.file.path','storage/queue');
            $dir = \base_path($rel);
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $f = $dir.'/._health';
            file_put_contents($f, '1');
            @unlink($f);
            return ['ok'=>true];
        } catch (\Throwable $e) {
            return ['ok'=>false,'error'=>$e->getMessage()];
        }
    }

    /** APP_KEY ve temel env kontrolü */
    public static function env(): array
    {
        $key = \Core\Env::get('APP_KEY','');
        if (!$key) return ['ok'=>false,'error'=>'APP_KEY missing'];
        return ['ok'=>true];
    }

    public static function summary(): array
    {
        return [
            'db'    => self::db(),
            'cache' => self::cache(),
            'mail'  => self::mail(),
            'queue' => self::queue(),
            'env'   => self::env(),
        ];
    }
}
