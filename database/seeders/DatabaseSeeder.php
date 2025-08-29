<?php
return new class {
    public function run(): void {
        // Diğer seeder’lar...
        $posts = require base_path('database/seeders/PostsSeeder.php');
        $posts->run();
    }
};
