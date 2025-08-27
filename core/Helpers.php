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
