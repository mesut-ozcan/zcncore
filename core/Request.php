<?php
namespace Core;

class Request
{
    public string $method;
    public string $uri;
    public array $query;
    public array $post;
    public array $files;
    public array $server;

    public static function capture(): self
    {
        $inst = new self();
        $inst->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $inst->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $inst->query = $_GET;
        $inst->post = $_POST;
        $inst->files = $_FILES;
        $inst->server = $_SERVER;
        return $inst;
    }

    public function path(): string { return $this->uri; }
    public function host(): string { return $this->server['HTTP_HOST'] ?? ''; }
    public function scheme(): string { return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http'; }
    public function fullUri(): string { return $this->server['REQUEST_URI'] ?? '/'; }
    public function url(string $path = '/'): string { return $this->scheme() . '://' . $this->host() . $path; }

    public function input(string $key, $default = null) {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }
}
