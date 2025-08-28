<?php
namespace Core;

class Response
{
    private int $status;
    /** @var array<int|string, string> */
    private array $headers;
    private string $body;

    /** @var callable|null */
    private $streamer = null;
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

    /** JSON yanıt; debug’da pretty-print */
    public static function json($data, int $status = 200, array $headers = []): self
    {
        $pretty = Config::get('app.debug', false);
        $opts = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($pretty) $opts |= JSON_PRETTY_PRINT;
        $json = json_encode($data, $opts);
        $h = array_merge(['Content-Type' => 'application/json; charset=UTF-8'], $headers);
        return new self((string)$json, $status, $h);
    }

    /** Dosya indirme (attachment) */
    public static function download(string $path, ?string $downloadName = null, ?string $mime = null): self
    {
        if (!is_file($path)) {
            return new self('<h1>404 Not Found</h1>', 404, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        $downloadName = $downloadName ?: basename($path);
        $mime = $mime ?: (function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream');

        $stat = @stat($path);
        $headers = [
            'Content-Type'            => $mime,
            'Content-Disposition'     => 'attachment; filename="'.$downloadName.'"',
            'X-Content-Type-Options'  => 'nosniff',
        ];
        if ($stat && isset($stat['size'])) {
            $headers['Content-Length'] = (string)$stat['size'];
        }

        $resp = new self('', 200, $headers);
        $resp->filePath = $path;
        return $resp;
    }

    /** Inline dosya sunumu (ETag/Last-Modified; 304 desteği) */
    public static function file(string $path, ?string $mime = null, string $disposition = 'inline', int $cacheSeconds = 3600): self
    {
        if (!is_file($path)) {
            return new self('<h1>404 Not Found</h1>', 404, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        $mime = $mime ?: (function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream');

        $stat = @stat($path) ?: [];
        $size = (int)($stat['size'] ?? 0);
        $mtime = (int)($stat['mtime'] ?? time());
        $etag = '"' . md5($size . '-' . $mtime) . '"';
        $lastMod = gmdate('D, d M Y H:i:s T', $mtime);

        $headers = [
            'Content-Type'    => $mime,
            'Cache-Control'   => 'public, max-age=' . $cacheSeconds,
            'ETag'            => $etag,
            'Last-Modified'   => $lastMod,
            'Content-Disposition' => $disposition . '; filename="' . basename($path) . '"',
        ];

        // Koşullu GET: If-None-Match / If-Modified-Since → 304
        $req = Request::current();
        $ifNoneMatch = $req ? trim($req->headers['If-None-Match'] ?? '') : '';
        $ifModSince  = $req ? trim($req->headers['If-Modified-Since'] ?? '') : '';
        $notModified = false;

        if ($ifNoneMatch !== '' && $ifNoneMatch === $etag) {
            $notModified = true;
        } elseif ($ifModSince !== '') {
            $ims = strtotime($ifModSince);
            if ($ims !== false && $mtime <= $ims) {
                $notModified = true;
            }
        }

        if ($notModified) {
            return new self('', 304, $headers);
        }

        $headers['Content-Length'] = (string)$size;

        $resp = new self('', 200, $headers);
        $resp->filePath = $path;
        return $resp;
    }

    /** Custom stream (callback echo/print yapabilir) */
    public static function stream(callable $callback, array $headers = [], int $status = 200): self
    {
        $resp = new self('', $status, $headers);
        $resp->streamer = $callback;
        return $resp;
    }

    /** No-cache header’ları uygula (zincirleme kullanılabilir) */
    public function noCache(): self
    {
        $this->headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
        $this->headers['Pragma'] = 'no-cache';
        $this->headers['Expires'] = '0';
        return $this;
    }

    /** Response’a Set-Cookie ekler (çoklu cookie desteklenir). */
    public function withCookie(string $name, string $value, int $minutes = 0, array $opts = []): self
    {
        $parts = [];
        $parts[] = rawurlencode($name) . '=' . rawurlencode($value);
        if ($minutes > 0) {
            $parts[] = 'Expires=' . gmdate('D, d M Y H:i:s T', time() + $minutes * 60);
            $parts[] = 'Max-Age=' . ($minutes * 60);
        }
        $parts[] = 'Path=' . ($opts['path'] ?? '/');
        if (!empty($opts['domain']))   $parts[] = 'Domain=' . $opts['domain'];
        if (!empty($opts['secure']))   $parts[] = 'Secure';
        if (($opts['httponly'] ?? true)) $parts[] = 'HttpOnly';
        $same = strtoupper((string)($opts['samesite'] ?? 'Lax'));
        if (in_array($same, ['LAX','STRICT','NONE'], true)) {
            $parts[] = 'SameSite=' . ucfirst(strtolower($same));
        } else {
            $parts[] = 'SameSite=Lax';
        }
        $cookieLine = 'Set-Cookie: ' . implode('; ', $parts);
        // Çoklu header için numerik index ile push
        $this->headers[] = $cookieLine;
        return $this;
    }

    /** Header ekle/override (zincirleme) */
    public function withHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /** Status kodu değiştir (zincirleme) */
    public function withStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /** Kernel / middleware kontrolleri için */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            if (is_int($k)) {
                header($v, false);
            } else {
                header($k . ': ' . $v, false);
            }
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
