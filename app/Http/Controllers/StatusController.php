<?php
namespace App\Http\Controllers;

use Core\Config;
use Core\Response;
use Core\Cache;
use Core\Logger;

class StatusController
{
    public function index(): Response
    {
        // Sadece debug açıkken göster
        $debug = Config::get('app.debug', false);
        if (!$debug) {
            return new Response('<h1>404 Not Found</h1>', 404);
        }

        $started = microtime(true);

        // Cache testi
        Cache::set('status_probe', 'ok', 30);
        $cacheOk = Cache::get('status_probe') === 'ok';

        // Log testi
        $logOk = false;
        try {
            Logger::info('status ping');
            $logOk = true;
        } catch (\Throwable $e) {
            $logOk = false;
        }

        // Storage yazılabilirlik testleri
        $paths = [
            'logs'    => base_path('storage/logs'),
            'cache'   => base_path('storage/cache'),
            'uploads' => base_path('storage/uploads'),
        ];
        $writable = [];
        foreach ($paths as $k => $p) {
            $writable[$k] = is_dir($p) && is_writable($p);
        }

        // Tema kontrolü
        $theme = Config::get('app.theme', 'default');
        $themeOk = is_dir(base_path("themes/$theme"));

        // Basit DB ping (opsiyonel)
        $db = Config::get('database', []);
        $dbStatus = ['enabled' => false, 'connected' => false, 'error' => null];
        if (!empty($db['name'])) {
            $dbStatus['enabled'] = true;
            try {
                $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
                $pdo = new \PDO($dsn, $db['user'], $db['pass'], [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]);
                $pdo->query('SELECT 1');
                $dbStatus['connected'] = true;
            } catch (\Throwable $e) {
                $dbStatus['error'] = $e->getMessage();
            }
        }

        $payload = [
            'ok'          => $cacheOk && $logOk && $themeOk,
            'app_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'env'         => Config::get('app.env', 'production'),
            'debug'       => $debug,
            'theme'       => $theme,
            'writable'    => $writable,
            'cache_ok'    => $cacheOk,
            'log_ok'      => $logOk,
            'db'          => $dbStatus,
            'elapsed_ms'  => (int) round((microtime(true) - $started) * 1000),
        ];

        return Response::json($payload, $payload['ok'] ? 200 : 500);
    }
}
