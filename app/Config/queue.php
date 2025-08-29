<?php
return [
    'driver' => env('QUEUE_DRIVER', 'file'),

    // file driver ayarları
    'file' => [
        'path'        => base_path('storage/queue'),      // kuyruk dosyaları
        'failed_path' => base_path('storage/queue_failed')// başarısız işler
    ],

    // worker davranışı
    'worker' => [
        'sleep'       => 2,   // kuyruk boşsa bekleme (saniye)
        'max_attempt' => 3,   // bir iş en fazla kaç kez denensin
        'stop_when_empty' => false, // true ise ilk boş kuyruğa düşmede worker durur
    ],
];