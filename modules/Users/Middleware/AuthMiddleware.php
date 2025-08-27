<?php
namespace Modules\Users\Middleware;

use Core\Request;
use Core\Response;

class AuthMiddleware
{
    public function __invoke(Request $req, callable $next)
    {
        if (empty($_SESSION['user'])) {
            return Response::redirect('/login');
        }
        return $next($req);
    }

    public static function checkRole(string $role): bool
    {
        $u = $_SESSION['user'] ?? null;
        return $u && isset($u['role']) && strtolower($u['role']) === strtolower($role);
    }
}
