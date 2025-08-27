<?php
namespace Core;

use Core\SEO\SitemapRegistry;
use Core\SEO\RobotsRegistry;
use Core\SEO\RedirectRegistry;

class Kernel
{
    private Application $app;
    private Router $router;

    public function __construct(Application $app)
    {
        $this->app = $app;
        \Core\Session::boot();
        $this->router = new Router();
        $this->registerErrorHandler();
        $this->registerBaseBindings();

        // Middleware aliases
        $this->router->alias('csrf', new \App\Middleware\CsrfMiddleware());
        $this->router->alias('throttle', new \App\Middleware\RateLimitMiddleware());
        if (class_exists(\Modules\Users\Middleware\AuthMiddleware::class)) {
            $this->router->alias('auth', new \Modules\Users\Middleware\AuthMiddleware());
        }
        if (class_exists(\Modules\Users\Middleware\AdminOnlyMiddleware::class)) {
            $this->router->alias('admin', new \Modules\Users\Middleware\AdminOnlyMiddleware());
        }

        $this->registerBaseRoutes();
        $this->loadModules();

        // Redirect rules from config
        $redirConfig = $this->app->basePath('app/Config/redirects.php');
        if (is_file($redirConfig)) {
            $rules = require $redirConfig;
            if (is_array($rules)) {
                foreach ($rules as $r) {
                    if (!empty($r['from']) && !empty($r['to'])) {
                        RedirectRegistry::add($r['from'], $r['to'], (int)($r['code'] ?? 301));
                    }
                }
            }
        }
    }

    private function registerErrorHandler(): void
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) return;
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (\Throwable $e) {
            $debug = Config::get('app.debug', false);
            Logger::error($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            $req = Request::current();
            $wantsJson = $req ? $req->wantsJson() : false;

            if ($wantsJson) {
                $payload = [
                    'ok' => false,
                    'error' => $debug ? (string)$e : 'Server Error',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'type' => (new \ReflectionClass($e))->getShortName(),
                ];
                (new Response(json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|($debug?JSON_PRETTY_PRINT:0)),
                    500, ['Content-Type' => 'application/json; charset=UTF-8']))->send();
            } else {
                $body = $debug
                    ? "<h1>Exception</h1><pre>{$e}</pre>"
                    : "<h1>Server Error</h1><p>Something went wrong.</p>";
                (new Response($body, 500))->send();
            }
            exit;
        });
    }

    private function registerBaseBindings(): void
    {
        $this->app->bind('router', $this->router);

        // Policies bootstrap: varsa çalıştır
        $policies = $this->app->basePath('app/Config/policies.php');
        if (is_file($policies)) {
            $fn = require $policies;
            if (is_callable($fn)) { $fn(); }
        }
    }

    private function registerBaseRoutes(): void
    {
        // Named home
        $this->router->getNamed('home', '/', [\App\Http\Controllers\HomeController::class, 'index']);

        // SEO endpoints
        $this->router->getNamed('sitemap', '/sitemap.xml', function () {
            $xml = SEO\SitemapRegistry::render();
            return (new Response($xml, 200, ['Content-Type' => 'application/xml']));
        });
        $this->router->getNamed('robots', '/robots.txt', function () {
            $txt = SEO\RobotsRegistry::render();
            return (new Response($txt, 200, ['Content-Type' => 'text/plain']));
        });

        // Status endpoint (debug)
        $this->router->getNamed('status', '/status', [\App\Http\Controllers\StatusController::class, 'index']);

        // Canonical host / trailing slash / CSRF cookie
        $this->router->middleware(function (Request $req, callable $next) {
            Csrf::ensureCookie();

            $hostCanonical = Config::get('app.canonical_host', '');
            $slashPolicy   = Config::get('app.trailing_slash', 'none');

            $uri  = $req->path();
            $host = $req->host();

            if ($redir = RedirectRegistry::check($req)) {
                return $redir;
            }

            if ($hostCanonical && $host !== $hostCanonical) {
                $url = $req->scheme() . '://' . $hostCanonical . $req->fullUri();
                return Response::redirect($url, 301);
            }

            if ($slashPolicy === 'add' && !str_ends_with($uri, '/')) {
                return Response::redirect($req->url('/') . '/', 301);
            }
            if ($slashPolicy === 'none' && $uri !== '/' && str_ends_with($uri, '/')) {
                return Response::redirect(rtrim($req->url('/'), '/'), 301);
            }

            return $next($req);
        });

        // Sitemap örnek kayıtları
        $base = rtrim(Config::get('app.url',''), '/');
        if ($base) {
            SEO\SitemapRegistry::add(['loc' => $base . route('home'), 'changefreq' => 'daily',  'priority' => '0.8']);
            SEO\SitemapRegistry::add(['loc' => $base . '/pages/hello-zcn', 'changefreq' => 'weekly', 'priority' => '0.6']);
        }
    }

    private function loadModules(): void
    {
        $modulesDir = $this->app->basePath('modules');
        if (!is_dir($modulesDir)) return;

        $overrides = [];
        $overridePath = $this->app->basePath('app/Config/modules.php');
        if (is_file($overridePath)) {
            $ov = require $overridePath;
            if (is_array($ov)) $overrides = $ov;
        }

        foreach (scandir($modulesDir) as $mod) {
            if ($mod === '.' || $mod === '..') continue;
            $modulePath = $modulesDir . '/' . $mod;
            $jsonPath   = $modulePath . '/module.json';

            if (is_dir($modulePath) && is_file($jsonPath)) {
                $meta = json_decode((string)file_get_contents($jsonPath), true) ?: [];
                $slug = $meta['slug'] ?? $mod;
                $enabled = $meta['enabled'] ?? true;
                if (array_key_exists($slug, $overrides)) {
                    $enabled = (bool)$overrides[$slug];
                }
                if (!$enabled) { continue; }

                $routes = $modulePath . '/routes.php';
                if (is_file($routes)) {
                    require $routes;
                }
            }
        }
    }

    public function handle(): Response
    {
        $request = Request::capture();
        return $this->router->dispatch($request);
    }    
}
