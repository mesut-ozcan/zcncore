<?php
namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Logger;

class RequestLogger
{
    /**
     * @param Request                       $req
     * @param callable(Request): Response   $next
     */
    public function __invoke(Request $req, callable $next): Response
    {
        $start = microtime(true);

        $resp  = $next($req);
        $durMs = number_format((microtime(true) - $start) * 1000, 2);

        // Güvenli ve Intelephense-dostu alanlar:
        $ip   = $req->server['HTTP_X_FORWARDED_FOR'] ?? ($req->server['REMOTE_ADDR'] ?? '-');
        $meth = $req->server['REQUEST_METHOD']       ?? 'GET';
        $path = method_exists($req, 'path') ? $req->path() : ($req->server['REQUEST_URI'] ?? '/');

        $status = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;

        Logger::info("{$ip} {$meth} {$path} {$durMs}ms status={$status}");

        return $resp;
    }
}
