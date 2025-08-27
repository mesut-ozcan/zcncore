<?php
namespace Core\Mail;

use Core\Config;

final class Mailer
{
    public static function send(string $to, string $subject, string $html, ?string $text = null, array $headersExtra = []): bool
    {
        $driver = (string)Config::get('mail.driver', 'mail');
        if ($driver === 'smtp') {
            return self::sendSmtp($to, $subject, $html, $text, $headersExtra);
        }
        return self::sendPhpMail($to, $subject, $html, $text, $headersExtra);
    }

    private static function buildHeaders(array $extra = []): array
    {
        $from = Config::get('mail.from', ['address'=>'no-reply@localhost','name'=>'ZCNCore']);
        $fromLine = sprintf('From: %s <%s>', $from['name'] ?? 'ZCNCore', $from['address'] ?? 'no-reply@localhost');

        $boundary = 'zcncore-'.bin2hex(random_bytes(8));
        $headers = [
            'MIME-Version' => '1.0',
            'Content-Type' => 'multipart/alternative; boundary="'.$boundary.'"',
            'X-Mailer'     => 'ZCNCore',
            $fromLine,
        ];

        foreach ($extra as $k=>$v) {
            if (is_int($k)) { $headers[] = $v; }
            else { $headers[$k] = $v; }
        }
        return [$headers, $boundary];
    }

    private static function composeBody(string $html, ?string $text, string $boundary): string
    {
        $text = $text ?? strip_tags($html);
        $eol = "\r\n";
        $body  = "--$boundary$eol";
        $body .= "Content-Type: text/plain; charset=UTF-8$eol$eol";
        $body .= $text.$eol;
        $body .= "--$boundary$eol";
        $body .= "Content-Type: text/html; charset=UTF-8$eol$eol";
        $body .= $html.$eol;
        $body .= "--$boundary--$eol";
        return $body;
    }

    private static function sendPhpMail(string $to, string $subject, string $html, ?string $text, array $headersExtra): bool
    {
        [$headersArr, $boundary] = self::buildHeaders($headersExtra);
        $body = self::composeBody($html, $text, $boundary);

        // headers string
        $lines = [];
        foreach ($headersArr as $k=>$v) {
            $lines[] = is_int($k) ? $v : ($k.': '.$v);
        }
        $headersStr = implode("\r\n", $lines);
        return @mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $body, $headersStr);
    }

    private static function sendSmtp(string $to, string $subject, string $html, ?string $text, array $headersExtra): bool
    {
        [$headersArr, $boundary] = self::buildHeaders($headersExtra);
        $body = self::composeBody($html, $text, $boundary);

        $smtp = Config::get('mail.smtp', []);
        $host = (string)($smtp['host'] ?? '127.0.0.1');
        $port = (int)($smtp['port'] ?? 25);
        $secure = $smtp['secure'] ?? null;
        $timeout= (int)($smtp['timeout'] ?? 10);
        $user   = $smtp['username'] ?? null;
        $pass   = $smtp['password'] ?? null;

        $remote = ($secure === 'ssl' ? "ssl://" : "") . $host;
        $fp = @fsockopen($remote, $port, $errno, $errstr, $timeout);
        if (!$fp) return false;

        $read = function() use ($fp) { return fgets($fp, 515); };
        $cmd  = function($line) use ($fp) { fwrite($fp, $line."\r\n"); };

        $greet = $read(); if (strpos($greet, '220') !== 0) { fclose($fp); return false; }
        $cmd('EHLO zcncore.local'); $ehlo = '';
        for ($i=0; $i<10; $i++) { $l = $read(); if (!$l) break; $ehlo .= $l; if (strpos($l,'250 ')===0) break; }

        if ($secure === 'tls') {
            $cmd('STARTTLS'); $line = $read(); if (strpos($line,'220')!==0) { fclose($fp); return false; }
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
            $cmd('EHLO zcncore.local'); for ($i=0; $i<10; $i++) { $l = $read(); if (!$l) break; if (strpos($l,'250 ')===0) break; }
        }

        if ($user && $pass) {
            $cmd('AUTH LOGIN'); if (strpos($read(),'334')!==0) { fclose($fp); return false; }
            $cmd(base64_encode($user)); if (strpos($read(),'334')!==0) { fclose($fp); return false; }
            $cmd(base64_encode($pass)); if (strpos($read(),'235')!==0) { fclose($fp); return false; }
        }

        $from = Config::get('mail.from', ['address'=>'no-reply@localhost']);
        $fromAddr = $from['address'] ?? 'no-reply@localhost';

        $cmd("MAIL FROM:<$fromAddr>"); if (strpos($read(),'250')!==0) { fclose($fp); return false; }
        $cmd("RCPT TO:<$to>");         if (strpos($read(),'250')!==0 && strpos($read(),'251')!==0) { fclose($fp); return false; }
        $cmd('DATA');                  if (strpos($read(),'354')!==0) { fclose($fp); return false; }

        // headers string (SMTP DATA)
        $lines = [];
        $lines[] = 'To: <'.$to.'>';
        $lines[] = 'Subject: ' . '=?UTF-8?B?'.base64_encode($subject).'?=';
        foreach ($headersArr as $k=>$v) {
            $lines[] = is_int($k) ? $v : ($k.': '.$v);
        }
        $data  = implode("\r\n", $lines) . "\r\n\r\n" . $body . "\r\n.";
        $cmd($data); if (strpos($read(),'250')!==0) { fclose($fp); return false; }

        $cmd('QUIT'); fclose($fp);
        return true;
    }
}
