<?php
return [
    'driver' => 'mail', // 'mail' | 'smtp'
    'from'   => ['address' => 'no-reply@localhost', 'name' => 'ZCNCore'],

    // SMTP ayarları (driver='smtp' ise kullanılır)
    'smtp' => [
        'host' => '127.0.0.1',
        'port' => 25,
        'username' => null,   // örn: 'user'
        'password' => null,   // örn: 'pass'
        'secure'   => null,   // null|'tls'|'ssl'
        'timeout'  => 10,
    ],
];
