<?php
namespace Modules\Admin\Http\Controllers;

use Core\Request;
use Core\Response;

final class AdminHomeController
{
    public function index(Request $req): Response
    {
        $basePath = app()->basePath();

        // uptime
        $uptime = microtime(true) - app()->startedAt();

        // cache dir size
        $cacheDir = $basePath . '/storage/cache';
        $cacheSize = 0;
        if (is_dir($cacheDir)) {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) { if ($f->isFile()) $cacheSize += $f->getSize(); }
        }

        $info = [
            'app_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'uptime_sec'  => (int)$uptime,
            'cache_bytes' => (int)$cacheSize,
        ];

        // view
        $title = 'Admin • Dashboard';
        ob_start();
        $data = $info; // view’de kullanılacak
        include base_path('modules/Admin/views/dashboard.php');
        $content = ob_get_clean();

        return new Response($content, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function clearCache(Request $req): Response
    {
        // CSRF (formdan _token geldiğini varsayıyoruz)
        if (!\Core\Csrf::check($req->post['_token'] ?? '')) {
            return Response::json(['ok' => false, 'error' => 'CSRF'], 419);
        }

        $dir = app()->basePath('storage/cache');
        $ok = true;
        if (is_dir($dir)) {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $f) {
                $ok = $f->isDir() ? @rmdir($f->getPathname()) && $ok : @unlink($f->getPathname()) && $ok;
            }
        }
        if ($ok) {
            \Core\Session::flash('success', 'Cache temizlendi.');
        } else {
            \Core\Session::flash('error', 'Cache temizlenirken hata oluştu.');
        }
        return Response::redirect('/admin');
    }
}