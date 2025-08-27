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
        $this->router = new Router();
        $this->registerErrorHandler();
        $this->registerBaseBindings();
        $this->registerBaseRoutes();
        $this->loadModules();
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
            $body = $debug
                ? "<h1>Exception</h1><pre>{$e}</pre>"
                : "<h1>Server Error</h1><p>Something went wrong.</p>";
            (new Response($body, 500))->send();
            exit;
        });
    }

    private function registerBaseBindings(): void
    {
        $this->app->bind('router', $this->router);
    }

    private function registerBaseRoutes(): void
    {
        // Home
        $this->router->get('/', [\App\Http\Controllers\HomeController::class, 'index']);

        // SEO endpoints
        $this->router->get('/sitemap.xml', function () {
            $xml = SitemapRegistry::render();
            return (new Response($xml, 200, ['Content-Type' => 'application/xml']));
        });

        $this->router->get('/robots.txt', function () {
            $txt = RobotsRegistry::render();
            return (new Response($txt, 200, ['Content-Type' => 'text/plain']));
        });

        // Canonical host / trailing slash normalizer
        $this->router->middleware(function (Request $req, callable $next) {
            $hostCanonical = Config::get('app.canonical_host', '');
            $slashPolicy = Config::get('app.trailing_slash', 'none'); // none|add

            $uri = $req->path();
            $host = $req->host();

            // Host normalize
            if ($hostCanonical && $host !== $hostCanonical) {
                $url = $req->scheme() . '://' . $hostCanonical . $req->fullUri();
                return Response::redirect($url, 301);
            }

            // Trailing slash normalize
            if ($slashPolicy === 'add' && !str_ends_with($uri, '/')) {
                return Response::redirect($req->url('/') . '/', 301);
            }
            if ($slashPolicy === 'none' && $uri !== '/' && str_ends_with($uri, '/')) {
                return Response::redirect(rtrim($req->url('/'), '/'), 301);
            }

            // Optional: custom redirects from registry
            if ($redir = RedirectRegistry::check($req)) {
                return $redir;
            }

            return $next($req);
        });
    }

    private function loadModules(): void
    {
        $modulesDir = $this->app->basePath('modules');
        if (!is_dir($modulesDir)) return;

        foreach (scandir($modulesDir) as $mod) {
            if ($mod === '.' || $mod === '..') continue;
            $modulePath = $modulesDir . '/' . $mod;
            if (is_dir($modulePath) && is_file($modulePath . '/module.json')) {
                // Simple register: include routes.php if exists
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
