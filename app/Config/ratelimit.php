<?php
return [
    'default' => ['limit' => 5, 'decay' => 60], // 5 istek / 60 sn
    'paths'   => [
        '#^/login$#'                => ['limit' => 5, 'decay' => 60],
        '#^/password/forgot$#'      => ['limit' => 3, 'decay' => 600],
        '#^/password/reset/.+$#'    => ['limit' => 5, 'decay' => 600],
    ],
];
