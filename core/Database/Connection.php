<?php
namespace Core\Database;

use PDO;
use PDOException;
use Core\Config;
use Core\Env;

/**
 * Basit PDO bağlantı yöneticisi (Singleton).
 * - .env veya app/Config/database.php üzerinden ayarlanır
 * - mysql sürücüsü varsayılan
 */
final class Connection
{
    private static ?Connection $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        // 1) Config dosyası varsa ordan oku
        $cfg = (array) Config::get('database', []);

        // 2) .env fallback (yoksa)
        $driver  = (string)($cfg['driver']   ?? 'mysql');
        $host    = (string)($cfg['host']     ?? Env::get('DB_HOST', '127.0.0.1'));
        $db      = (string)($cfg['database'] ?? Env::get('DB_NAME', 'zcncore'));
        $user    = (string)($cfg['username'] ?? Env::get('DB_USER', 'root'));
        $pass    = (string)($cfg['password'] ?? Env::get('DB_PASS', ''));
        $charset = (string)($cfg['charset']  ?? Env::get('DB_CHARSET', 'utf8mb4'));
        $port    = (string)($cfg['port']     ?? Env::get('DB_PORT', '3306'));

        // DSN oluştur
        if ($driver === 'mysql') {
            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        } elseif ($driver === 'sqlite') {
            $path = $cfg['path'] ?? (Env::get('DB_PATH', __DIR__ . '/../../storage/database.sqlite'));
            $dsn = "sqlite:" . $path;
            $user = null; $pass = null;
        } else {
            // ihtiyaç olursa genişletilebilir
            throw new \RuntimeException("Unsupported DB driver: {$driver}");
        }

        // PDO options (config > defaults)
        $options = (array)($cfg['options'] ?? []);
        $defaults = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        // config’te verilenler default’ları override eder
        foreach ($defaults as $k=>$v) {
            if (!array_key_exists($k, $options)) $options[$k] = $v;
        }

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new \RuntimeException('DB connection failed: ' . $e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    // İsteğe bağlı yardımcılar:
    public function begin(): void { $this->pdo->beginTransaction(); }
    public function commit(): void { $this->pdo->commit(); }
    public function rollBack(): void { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
