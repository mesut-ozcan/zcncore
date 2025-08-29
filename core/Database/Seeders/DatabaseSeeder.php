<?php
namespace Core\Database\Seeders;

use Core\Database\Seeder;
use PDO;

/**
 * Framework içi örnek DatabaseSeeder
 * Kullanıcı app/Database/Seeders/DatabaseSeeder ile override edebilir.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(PDO $pdo): void
    {
        // Varsayılan olarak boş
        // Kullanıcı kendi app/Database/Seeders/DatabaseSeeder ile zincir çalıştırır.
    }
}