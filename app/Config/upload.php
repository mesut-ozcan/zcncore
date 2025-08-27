<?php
return [
    'max_bytes' => 5 * 1024 * 1024, // 5MB
    'allowed_ext' => ['jpg','jpeg','png','gif','webp','pdf','txt'],
    'allowed_mime' => [
        'image/jpeg','image/png','image/gif','image/webp',
        'application/pdf','text/plain'
    ],
    // alt dizin: storage/uploads/YYYY/MM
    'base_dir' => 'storage/uploads',

    // Görsel özel kurallar
    'images' => [
        'max_width'  => 4096,
        'max_height' => 4096,
    ],
];
