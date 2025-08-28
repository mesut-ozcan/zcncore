<?php
return [
    'driver'    => env('SESSION_DRIVER', 'file'), // 'file' | 'database'
    'lifetime'  => (int)env('SESSION_LIFETIME', 120), // dakika
    'cookie'    => env('SESSION_COOKIE', 'zcn_session'),
    'path'      => '/',
    'domain'    => env('SESSION_DOMAIN', ''), // boş ise otomatik
    'secure'    => (bool)env('SESSION_SECURE', false),
    'httponly'  => true,
    'samesite'  => env('SESSION_SAMESITE', 'Lax'), // Lax|Strict|None
    // file driver
    'files'     => 'storage/sessions',
    // database driver
    'connection'=> null, // varsayılan bağlantı
    'table'     => 'sessions',
];
