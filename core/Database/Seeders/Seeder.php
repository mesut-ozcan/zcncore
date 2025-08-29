<?php
namespace Core\Database\Seeders;

use PDO;

abstract class Seeder
{
    abstract public function run(PDO $pdo): void;

    protected function insertMany(PDO $pdo, string $table, array $rows): void
    {
        if (empty($rows)) return;
        $cols = array_keys($rows[0]);
        $ph = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql = "INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES "
             . implode(',', array_fill(0, count($rows), $ph));

        $values = [];
        foreach ($rows as $r) foreach ($cols as $c) $values[] = $r[$c] ?? null;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
}