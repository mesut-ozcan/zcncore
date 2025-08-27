<?php
namespace Modules\Logs\Http\Controllers;

use Core\Response;

class LogController
{
    private function logFile(): string
    {
        return base_path('storage/logs/app.log');
    }

    private function tail(string $file, int $lines = 200): string
    {
        if (!is_file($file)) return '';
        $fp = fopen($file, 'rb');
        if (!$fp) return '';

        $buffer = '';
        $chunkSize = 2048;
        $pos = -1;
        $lineCount = 0;
        fseek($fp, 0, SEEK_END);
        $filesize = ftell($fp);

        while ($lineCount < $lines && $filesize > 0) {
            $seek = max($filesize - $chunkSize, 0);
            $read = $filesize - $seek;
            fseek($fp, $seek);
            $buf = fread($fp, $read);
            $buffer = $buf . $buffer;
            $filesize = $seek;
            $lineCount = substr_count($buffer, "\n");
            if ($seek === 0) break;
        }
        fclose($fp);

        $rows = explode("\n", trim($buffer));
        $rows = array_slice($rows, -$lines);
        return implode("\n", $rows);
    }

    public function index(): Response
    {
        $file = $this->logFile();
        $n    = max(10, (int)($_GET['n'] ?? 200));
        $q    = trim($_GET['q'] ?? '');
        $raw  = $this->tail($file, $n);

        $lines = $raw === '' ? [] : explode("\n", $raw);
        if ($q !== '') {
            $lines = array_values(array_filter($lines, function($l) use ($q){
                return stripos($l, $q) !== false;
            }));
        }

        return new Response(view('Logs::index', [
            'title' => 'Log Viewer',
            'file_exists' => is_file($file),
            'n' => $n,
            'q' => $q,
            'content' => implode("\n", $lines)
        ]));
    }

    public function download(): Response
    {
        $file = $this->logFile();
        if (!is_file($file)) {
            return new Response('<h1>Log bulunamadı</h1>', 404);
        }
        $content = (string) file_get_contents($file);
        $headers = [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="app.log"'
        ];
        return new Response($content, 200, $headers);
    }
}
