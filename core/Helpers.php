<?php
use Core\Application;
use Core\Config;
use Core\View;
use Core\Csrf;
use Core\Head;

function app(): Core\Application { return Application::get(); }
function base_path(string $p=''): string { return app()->basePath($p); }
function config(string $key, $default=null){ return Config::get($key, $default); }
function env(string $key, $default=null){ return \Core\Env::get($key, $default); }
function view(string $name, array $data=[]): string { return View::render($name, $data); }
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function csrf_field(): string { return '<input type="hidden" name="_token" value="'.e(Csrf::token()).'">'; }
function asset(string $path): string { return rtrim(config('app.url', ''), '/') . '/themes/' . config('app.theme', 'default') . '/' . ltrim($path, '/'); }
function route(string $path='/', array $params=[]): string {
    $url = rtrim(config('app.url',''), '/') . $path;
    if ($params) $url .= '?' . http_build_query($params);
    return $url;
}
function head(): Head { return new Head(); }
