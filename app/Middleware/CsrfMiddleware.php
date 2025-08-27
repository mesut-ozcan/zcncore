<?php
namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Csrf;

class CsrfMiddleware
{
    public function __invoke(Request $req, callable $next)
    {
        if (strtoupper($req->method) === 'POST') {
            $token = $_POST['_token'] ?? '';
            if (!Csrf::check($token)) {
                return new Response('<h1>419 Page Expired (CSRF)</h1>', 419);
            }
        }
        return $next($req);
    }
}
