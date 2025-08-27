<?php
return [
    // APP_KEY (base64) anahtarından türetilir; burada sadece ayarlar var
    'remember_cookie' => 'zcn_remember',
    'remember_ttl_days' => 30, // kaç gün hatırlansın
    'cipher' => 'AES-256-CBC',
    // domain/path/samesite/secure/httponly cookie ayarları (opsiyonel override)
    'cookie' => [
        'path' => '/',
        'domain' => '',
        'secure' => null,   // null: otomatik (https ise true)
        'httponly' => true,
        'samesite' => 'Lax',
    ],
];
