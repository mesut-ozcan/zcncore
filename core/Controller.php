<?php
namespace Core;

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

    /**
     * Basit yetki kontrolü: rol veya callback
     * @param string|array|callable $ability
     */
    protected function authorize($ability): void
    {
        $user = $_SESSION['user'] ?? null;

        if (is_callable($ability)) {
            if (!$ability($user)) {
                throw new \RuntimeException('Unauthorized', 403);
            }
            return;
        }

        $roles = (array)$ability;
        $ok = false;
        foreach ($roles as $r) {
            if ($user && strtolower(($user['role'] ?? '')) === strtolower($r)) { $ok = true; break; }
        }
        if (!$ok) {
            throw new \RuntimeException('Unauthorized', 403);
        }
    }
}
