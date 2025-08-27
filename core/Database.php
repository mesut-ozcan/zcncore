<?php
namespace Core;

final class Database
{
    private static ?\PDO $pdo = null;

    public static function pdo(): \PDO
    {
        if (!self::$pdo) {
            $cfg = Config::get('database');
            if (empty($cfg['name'])) {
                throw new \RuntimeException('Database is not configured. Set DB_NAME in .env');
            }
            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}";
            self::$pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        }
        return self::$pdo;
    }
}
