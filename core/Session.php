<?php
namespace Core;

use Core\Database\Connection;

class Session
{
    private static bool $booted = false;
    private static string $id;
    private static array $data = [];
    private static int $lifetime;
    private static string $cookieName;
    private static array $cookieOpts;
    private static string $driver;
    private static ?string $filesDir = null;
    private static ?string $dbTable  = null;

    public static function boot(): void
    {
        if (self::$booted) return;

        $cfg = Config::get('session', []);
        self::$driver     = (string)($cfg['driver'] ?? 'file');
        self::$lifetime   = (int)($cfg['lifetime'] ?? 120);
        self::$cookieName = (string)($cfg['cookie'] ?? 'zcn_session');

        self::$cookieOpts = [
            'path'     => $cfg['path'] ?? '/',
            'domain'   => $cfg['domain'] ?: '',
            'secure'   => (bool)($cfg['secure'] ?? false),
            'httponly' => (bool)($cfg['httponly'] ?? true),
            'samesite' => $cfg['samesite'] ?? 'Lax',
        ];

        if (self::$driver === 'file') {
            self::$filesDir = base_path($cfg['files'] ?? 'storage/sessions');
            if (!is_dir(self::$filesDir)) @mkdir(self::$filesDir, 0777, true);
        } elseif (self::$driver === 'database') {
            self::$dbTable = $cfg['table'] ?? 'sessions';
        }

        // ID seç
        $id = $_COOKIE[self::$cookieName] ?? '';
        self::$id = (is_string($id) && preg_match('/^[a-f0-9]{40}$/', $id)) ? $id : self::newId();

        // Yükle
        self::$data = self::read(self::$id);

        // Cookie yenile (sliding expiration)
        $minutes = max(1, self::$lifetime);
        self::queueCookie(self::$cookieName, self::$id, $minutes);

        self::$booted = true;
    }

    private static function newId(): string
    {
        return bin2hex(random_bytes(20));
    }

    private static function filePath(string $id): string
    {
        return rtrim(self::$filesDir ?? base_path('storage/sessions'), '/\\') . DIRECTORY_SEPARATOR . $id . '.sess';
    }

    private static function read(string $id): array
    {
        $now = time();
        if (self::$driver === 'file') {
            $file = self::filePath($id);
            if (is_file($file)) {
                $raw = file_get_contents($file);
                $arr = $raw ? @unserialize($raw) : null;
                if (is_array($arr)) {
                    // expire kontrol
                    if (($arr['_expires'] ?? 0) > $now) {
                        return $arr['data'] ?? [];
                    }
                }
            }
            return [];
        }

        if (self::$driver === 'database') {
            $pdo = Connection::getInstance()->pdo();
            $tbl = self::$dbTable ?? 'sessions';
            $stmt = $pdo->prepare("SELECT payload, expires_at FROM {$tbl} WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                if ((int)$row['expires_at'] > $now) {
                    $arr = @unserialize((string)$row['payload']);
                    return is_array($arr) ? $arr : [];
                }
            }
            return [];
        }

        return [];
    }

    private static function write(): void
    {
        $now = time();
        $exp = $now + (self::$lifetime * 60);
        if (self::$driver === 'file') {
            $file = self::filePath(self::$id);
            $payload = serialize(['data'=>self::$data,'_expires'=>$exp]);
            @file_put_contents($file, $payload);
            return;
        }

        if (self::$driver === 'database') {
            $pdo = Connection::getInstance()->pdo();
            $tbl = self::$dbTable ?? 'sessions';
            $payload = serialize(self::$data);
            $pdo->prepare("REPLACE INTO {$tbl} (id, payload, last_activity, expires_at) VALUES (?, ?, ?, ?)")
                ->execute([self::$id, $payload, $now, $exp]);
            return;
        }
    }

    private static function queueCookie(string $name, string $value, int $minutes): void
    {
        $opts = self::$cookieOpts;
        $expires = gmdate('D, d M Y H:i:s T', time() + ($minutes * 60));
        $parts = [
            rawurlencode($name) . '=' . rawurlencode($value),
            'Path=' . ($opts['path'] ?? '/'),
            'Expires=' . $expires,
            'Max-Age=' . ($minutes * 60),
        ];
        if (!empty($opts['domain']))   $parts[] = 'Domain=' . $opts['domain'];
        if (!empty($opts['secure']))   $parts[] = 'Secure';
        if (!empty($opts['httponly'])) $parts[] = 'HttpOnly';
        $same = ucfirst(strtolower($opts['samesite'] ?? 'Lax'));
        $parts[] = 'SameSite=' . (in_array($same, ['Lax','Strict','None']) ? $same : 'Lax');
        header('Set-Cookie: ' . implode('; ', $parts), false);
    }

    public static function save(): void
    {
        if (!self::$booted) return;
        self::write();
    }

    public static function id(): string { return self::$id; }
    public static function get(string $key, $default=null) { return self::$data[$key] ?? $default; }
    public static function put(string $key, $value): void { self::$data[$key] = $value; }
    public static function forget(string $key): void { unset(self::$data[$key]); }
    public static function all(): array { return self::$data; }
    public static function flush(): void { self::$data = []; }

    /** Flash helpers */
    public static function flash(string $key, $value): void {
        $f = self::$data['_flash'] ?? [];
        $f[$key] = $value;
        self::$data['_flash'] = $f;
    }
    public static function getFlash(string $key, $default=null) {
        $f = self::$data['_flash'] ?? [];
        $v = $f[$key] ?? $default;
        // okununca sil
        if (isset($f[$key])) {
            unset($f[$key]);
            self::$data['_flash'] = $f;
        }
        return $v;
    }

    /* ---------- Helpers.php ile uyumlu proxy metodlar ---------- */

    /** @deprecated: Helpers backward-compat — flash değerini alır ve siler */
    public static function pullFlash(string $key, $default = null) {
        return self::getFlash($key, $default);
    }

    /** Form eski inputlarını set et (controller veya middleware’de) */
    public static function setOld(array $data): void
    {
        self::$data['_old'] = $data;
    }

    /** Eski input’u al */
    public static function old(string $key, $default = null)
    {
        $old = self::$data['_old'] ?? [];
        return $old[$key] ?? $default;
    }

    /** Hata listelerini set et (key => [mesaj1, mesaj2...]) */
    public static function setErrors(array $errors): void
    {
        self::$data['_errors'] = $errors;
    }

    /**
     * Hata listelerini al.
     * - $key verilirse: o alanın hata dizisini döndürür (yoksa boş dizi)
     * - $key null ise: tüm hata dizisini döndürür (yoksa boş dizi)
     */
    public static function errors(?string $key = null)
    {
        $errs = self::$data['_errors'] ?? [];
        if ($key === null) return $errs;
        return $errs[$key] ?? [];
    }

}
