<?php
namespace App\Middleware;

use Core\Csrf;
use Core\Request;
use Core\Response;
use Core\Session;

class CsrfMiddleware
{
    public function __invoke(Request $req, callable $next)
    {
        if (strtoupper($req->method) !== 'POST') {
            return $next($req);
        }

        $token = $req->post['_token'] ?? $req->headers['X-CSRF-TOKEN'] ?? $req->headers['X-XSRF-TOKEN'] ?? '';
        if (!Csrf::check((string)$token)) {
            $wants = $req->wantsJson();

            if ($wants) {
                // JSON standart hata (419 Page Expired)
                return Response::json([
                    'ok' => false,
                    'error' => 'CSRF token mismatch',
                    'code' => 419,
                ], 419);
            }

            // HTML: old input’ı koru + hata mesajını flash et ve geri dön
            Session::setOld($req->all());
            Session::flash('error', 'Form süresi doldu. Lütfen tekrar deneyin.');
            $back = $req->server['HTTP_REFERER'] ?? $req->path();
            return Response::redirect($back, 302)->noCache();
        }

        return $next($req);
    }
}
