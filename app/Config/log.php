<?php
return [
    'path'            => 'storage/logs',
    'retention_days'  => 14,      // kaç gün geriye kadar tutulsun
    'level'           => 'debug', // debug|info|warning|error
    'filename'        => 'app-{Y-m-d}.log', // günlük isim şablonu
];
