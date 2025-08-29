<?php
namespace Core\Database;

use PDO;

abstract class Seeder
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Connection::getInstance()->pdo();
    }

    abstract public function run(): void;
}