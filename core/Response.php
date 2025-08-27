<?php
namespace Core;

class Response
{
    private int $status;
    private array $headers;
    private string $body;

    /** @var callable|null */
    private $streamer = null;   // <— callable tipini property’de kullanamıyoruz
    private ?string $filePath = null;

    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function redirect(string $url, int $code = 302): self
    {
        return new self('', $code, ['Location' => $url]);
    }

    public static function json($data, int $status = 200, array $headers = []): self
    {
        $pretty = Config::get('app.debug', false);
        $opts = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) $opts |= JSON_PRETTY_PRINT;
        $json = json_encode($data, $opts);
        $h = array_merge(['Content-Type' => 'application/json; charset=UTF-8'], $headers);
        return new self((string)$json, $status, $h);
    }

    public static function download(string $path, ?string $downloadName = null, ?string $mime = null): self
    {
        if (!is_file($path)) {
            return new self('<h1>404 Not Found</h1>', 404, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        $downloadName = $downloadName ?: basename($path);
        $mime = $mime ?: (function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream');

        $resp = new self('', 200, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'attachment; filename="'.$downloadName.'"',
            'X-Content-Type-Options'    => 'nosniff',
        ]);
        $resp->filePath = $path;
        return $resp;
    }

    public static function stream(callable $callback, array $headers = [], int $status = 200): self
    {
        $resp = new self('', $status, $headers);
        $resp->streamer = $callback;
        return $resp;
    }

    public function noCache(): self
    {
        $this->headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
        $this->headers['Pragma'] = 'no-cache';
        $this->headers['Expires'] = '0';
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }

        if ($this->filePath) {
            $fp = fopen($this->filePath, 'rb');
            if ($fp) {
                while (!feof($fp)) {
                    echo fread($fp, 8192);
                }
                fclose($fp);
            }
            return;
        }

        if ($this->streamer && is_callable($this->streamer)) {
            ($this->streamer)();
            return;
        }

        echo $this->body;
    }
}
