<?php
namespace Modules\Admin\Http\Controllers;

use Core\Request;
use Core\Response;

final class LogsController
{
    private function logsDir(): string
    {
        return app()->basePath('storage/logs');
    }

    private function listLogs(): array
    {
        $dir = $this->logsDir();
        if (!is_dir($dir)) return [];
        $files = [];
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            if (!str_ends_with($f, '.log')) continue;
            $files[] = $f;
        }
        sort($files);
        return array_reverse($files); // en yeniler başta
    }

    private function safePath(string $file): ?string
    {
        // sadece bu klasör ve .log uzantısı
        if (!preg_match('/^[a-z0-9._-]+\.log$/i', $file)) return null;
        $path = realpath($this->logsDir() . '/' . $file);
        if (!$path) return null;
        // path traversal engeli
        if (!str_starts_with($path, realpath($this->logsDir()))) return null;
        return $path;
    }

    public function index(Request $req): Response
    {
        $files = $this->listLogs();

        // seçili dosya
        $selected = $req->get['file'] ?? ($files[0] ?? null);
        $tail = [];
        if ($selected) {
            $path = $this->safePath($selected);
            if ($path && is_file($path)) {
                // son N satırı oku (200)
                $tail = $this->tailFile($path, 200);
            }
        }

        $title = 'Admin • Logs';
        ob_start();
        $data = ['files' => $files, 'selected' => $selected, 'tail' => $tail];
        include base_path('modules/Admin/views/logs.php');
        $content = ob_get_clean();

        return new Response($content, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function download(Request $req): Response
    {
        $file = (string)($req->get['file'] ?? '');
        $path = $this->safePath($file);
        if (!$path || !is_file($path)) {
            return new Response('<h1>404</h1>', 404);
        }
        return Response::download($path, $file, 'text/plain');
    }

    private function tailFile(string $file, int $lines): array
    {
        // basit tail: satırları son N satırla sınırla
        $buffer = 4096;
        $f = @fopen($file, 'rb');
        if (!$f) return [];
        $pos = -1;
        $lineCount = 0;
        $output = '';
        fseek($f, 0, SEEK_END);
        $filesize = ftell($f);

        while ($lineCount < $lines && -$pos < $filesize) {
            $step = min($buffer, $filesize + $pos);
            $pos -= $step;
            fseek($f, $pos, SEEK_END);
            $chunk = fread($f, $step);
            $output = $chunk . $output;
            $lineCount = substr_count($output, "\n");
        }
        fclose($f);
        $rows = explode("\n", trim($output));
        $len = count($rows);
        return array_slice($rows, max(0, $len - $lines));
    }
}