<?php
/**
 * Global helper functions for ZCNCore
 */

if (!function_exists('app')) {
    function app(): \Core\Application {
        return \Core\Application::get();
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string {
        return app()->basePath($path);
    }
}

if (!function_exists('config')) {
    /**
     * Dot-notation config getter. Example: config('app.debug', false)
     */
    function config(string $key, $default = null) {
        return \Core\Config::get($key, $default);
    }
}

if (!function_exists('e')) {
    /**
     * HTML escape
     */
    function e($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * View resolver:
 * - "Module::view/name" => önce theme override: themes/<theme>/modules/<Module>/views/<view/name>.php
 *                          yoksa modules/<Module>/Views/<view/name>.php
 * - "path/to/view"       => önce theme override: themes/<theme>/views/<path/to/view>.php
 *                          yoksa app/Views/<path/to/view>.php
 */
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
            if (is_file($file)) {
                extract($data, EXTR_SKIP);
                ob_start();
                include $file;
                return ob_get_clean();
            }
        }

        // Hata mesajında denenen yolları göster
        $msg = "View not found: {$name}\nTried:\n - " . implode("\n - ", $candidates);
        throw new \RuntimeException($msg);
    }
}

if (!function_exists('head')) {
    /**
     * @deprecated Doğrudan static kullanım tercih edilir: \Core\Head::render()
     * Bu helper, Intelephense için gerçek \Core\Head tipi döndürür.
     */
    function head(): \Core\Head {
        // Head statik bir sınıf gibi kullanılıyor; instance döndürmek güvenlidir.
        return new \Core\Head();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return \Core\Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="_token" value="'.e(\Core\Csrf::token()).'">';
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * SPA'ler için meta tag (JS ile header'a X-CSRF-TOKEN göndermek için okunabilir)
     */
    function csrf_meta(): string {
        return '<meta name="csrf-token" content="'.e(\Core\Csrf::token()).'">';
    }
}
