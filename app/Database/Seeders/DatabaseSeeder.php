<?php
namespace App\Database\Seeders;

use Core\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // sıra önemliyse burada belirle
        \Core\Database\SeedRunner::run([
            UsersSeeder::class,
            PagesSeeder::class,
        ]);
    }
}