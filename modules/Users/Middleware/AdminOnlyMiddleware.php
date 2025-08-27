<?php
namespace Modules\Users\Middleware;

use Core\Request;
use Core\Response;

class AdminOnlyMiddleware
{
    public function __invoke(Request $req, callable $next)
    {
        $u = $_SESSION['user'] ?? null;
        if (!$u || strtolower($u['role'] ?? '') !== 'admin') {
            // yetkisiz → hesap sayfasına at veya 403 dön
            return new Response('<h1>403 Forbidden</h1>', 403);
        }
        return $next($req);
    }
}
