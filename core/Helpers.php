<?php
/**
 * Global helper functions for ZCNCore
 */

if (!function_exists('app')) {
    function app(): \Core\Application { return \Core\Application::get(); }
}
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string { return app()->basePath($path); }
}
if (!function_exists('config')) {
    function config(string $key, $default = null) { return \Core\Config::get($key, $default); }
}
if (!function_exists('e')) {
    function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('view')) {
    function view(string $name, array $data = []): string
    {
        $theme = (string) config('app.theme', 'default');
        $candidates = [];
        if (strpos($name, '::') !== false) {
            [$module, $view] = explode('::', $name, 2);
            $vp = str_replace(['.', '/'], DIRECTORY_SEPARATOR, $view);
            $candidates[] = base_path("themes/{$theme}/modules/{$module}/views/{$vp}.php");
            $candidates[] = base_path("modules/{$module}/Views/{$vp}.php");
        } else {
            $vp = str_replace(['.', '/'], DIRECTORY_SEPARATOR, $name);
            $candidates[] = base_path("themes/{$theme}/views/{$vp}.php");
            $candidates[] = base_path("app/Views/{$vp}.php");
        }
        foreach ($candidates as $file) {
            if (is_file($file)) { extract($data, EXTR_SKIP); ob_start(); include $file; return ob_get_clean(); }
        }
        throw new \RuntimeException("View not found: {$name}\n - ".implode("\n - ", $candidates));
    }
}
if (!function_exists('head')) {
    /** @deprecated \Core\Head::render() kullan */
    function head(): \Core\Head { return new \Core\Head(); }
}
if (!function_exists('csrf_token')) {
    function csrf_token(): string { return \Core\Csrf::token(); }
}
if (!function_exists('csrf_field')) {
    function csrf_field(): string { return '<input type="hidden" name="_token" value="'.e(\Core\Csrf::token()).'">'; }
}
if (!function_exists('csrf_meta')) {
    function csrf_meta(): string { return '<meta name="csrf-token" content="'.e(\Core\Csrf::token()).'">'; }
}

/** Cookie helpers */
if (!function_exists('cookie_get')) {
    function cookie_get(string $name, $default = null) {
        return $_COOKIE[$name] ?? $default;
    }
}
if (!function_exists('cookie_set')) {
    /**
     * Hızlı cookie set. Response objesine eklemek istersen:
     *   return Response::json(...)->withCookie('name','val',60);
     */
    function cookie_set(string $name, string $value, int $minutes = 0, array $opts = []): bool
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
        $params = [
            'expires'  => $minutes > 0 ? (time() + $minutes * 60) : 0,
            'path'     => $opts['path'] ?? '/',
            'domain'   => $opts['domain'] ?? '',
            'secure'   => $opts['secure'] ?? $secure,
            'httponly' => $opts['httponly'] ?? true,
            'samesite' => $opts['samesite'] ?? 'Lax',
        ];
        return @setcookie($name, $value, $params);
    }
}
if (!function_exists('route')) {
    /**
     * Named route URL helper.
     * usage: route('login'), route('page.show', ['slug'=>'hello'])
     */
    function route(string $name, array $params = [], bool $absolute = false): string {
        /** @var \Core\Router $router */
        $router = app()->make('router');
        if (!$router) throw new \RuntimeException('Router not bound.');
        return $router->urlFor($name, $params, $absolute);
    }
}
if (!function_exists('component')) {
    function component(string $name, array $data = []): string {
        return \Core\View::component($name, $data);
    }
}