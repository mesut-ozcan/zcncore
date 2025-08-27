<?php
namespace Core;

use Core\Auth\Gate;

abstract class Controller
{
    protected function view(string $name, array $data = []): Response
    {
        return new Response(view($name, $data));
    }

    protected function json($data, int $status = 200, array $headers = []): Response
    {
        return Response::json($data, $status, $headers);
    }

    protected function redirect(string $to, int $code = 302): Response
    {
        return Response::redirect($to, $code);
    }

    /** Rol temelli basit kontrol (BC) */
    protected function authorize($ability): void
    {
        $user = $_SESSION['user'] ?? null;

        if (is_callable($ability)) {
            if (!$ability($user)) throw new \RuntimeException('Unauthorized', 403);
            return;
        }

        $roles = (array)$ability;
        foreach ($roles as $r) {
            if ($user && strtolower(($user['role'] ?? '')) === strtolower($r)) return;
        }
        throw new \RuntimeException('Unauthorized', 403);
    }

    /** Gate/Policy ile kontrol (önerilen) */
    protected function authorizeGate(string $ability, array $args = []): void
    {
        Gate::authorize($ability, $args);
    }
}
