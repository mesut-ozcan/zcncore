<?php
namespace Core;

class Response
{
    private string $content;
    private int $status;
    private array $headers;

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function json($data, int $status = 200): self
    {
        return new self(json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), $status, ['Content-Type' => 'application/json']);
    }

    public static function redirect(string $to, int $status = 302): self
    {
        return new self('', $status, ['Location' => $to]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header("$k: $v");
        }
        echo $this->content;
    }
}
