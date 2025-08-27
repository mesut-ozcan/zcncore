<?php
namespace Core;

use Core\Validation\Validator;

class Request
{
    public string $method;
    public array $get;
    public array $post;
    public array $server;
    public array $files;
    public array $cookies;
    public array $headers;

    private static ?Request $current = null;

    public static function capture(): Request
    {
        $req = new Request();
        $req->method  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $req->get     = $_GET;
        $req->post    = $_POST;
        $req->server  = $_SERVER;
        $req->files   = $_FILES ?? [];
        $req->cookies = $_COOKIE ?? [];
        $req->headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        self::$current = $req;
        return $req;
    }

    public static function current(): ?Request
    {
        return self::$current;
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $qpos = strpos($uri, '?');
        if ($qpos !== false) $uri = substr($uri, 0, $qpos);
        return $uri ?: '/';
    }

    public function fullUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function scheme(): string
    {
        $https = $this->server['HTTPS'] ?? 'off';
        return ($https !== 'off' && $https !== '') ? 'https' : 'http';
    }

    public function host(): string
    {
        return $this->server['HTTP_HOST'] ?? 'localhost';
    }

    public function url(string $path = '/'): string
    {
        return $this->scheme() . '://' . $this->host() . $path;
    }

    // --- Helpers ---
    public function input(?string $key = null, $default = null)
    {
        $all = array_merge($this->get, $this->post);
        if ($key === null) return $all;
        return $all[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /** @return array{valid:bool, data:array, errors:array} */
    public function validate(array $rules): array
    {
        return Validator::make($this->all(), $rules);
    }

    public function wantsJson(): bool
    {
        $accept = strtolower($this->headers['Accept'] ?? '');
        $xhr    = strtolower($this->headers['X-Requested-With'] ?? '');
        $ctype  = strtolower($this->headers['Content-Type'] ?? '');
        return (
            str_contains($accept, 'application/json') ||
            $xhr === 'xmlhttprequest' ||
            str_contains($ctype, 'application/json')
        );
    }

    public function expectsJson(): bool
    {
        return $this->wantsJson();
    }
}
