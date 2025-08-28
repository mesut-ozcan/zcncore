<?php
return [
    // global default (dakika başına)
    'default' => ['max' => 60, 'per' => 60],
    // rota bazlı (path regex ya da named route)
    'routes' => [
        // örnek: login & forgot & reset POST
        ['match' => '#^/login$#',           'methods'=>['POST'], 'max'=>10, 'per'=>60],
        ['match' => '#^/password/forgot$#', 'methods'=>['POST'], 'max'=>5,  'per'=>300],
        ['match' => '#^/password/reset$#',  'methods'=>['POST'], 'max'=>5,  'per'=>300],
        // API örneği
        // ['match' => '#^/api/#', 'methods'=>['GET','POST'], 'max'=>120, 'per'=>60],
    ],
];
