<?php
/**
 * Basit yönlendirme kuralları.
 * format örneği:
 * [
 *   ['from' => '#^/eski-url/?$#', 'to' => '/yeni-url', 'code' => 301],
 * ]
 */
return [
    // /old-page -> /pages/hello-zcn
    ['from' => '#^/old-page/?$#', 'to' => '/pages/hello-zcn', 'code' => 301],
];
