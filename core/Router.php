<?php
namespace Core;

class Router
{
    private array $routes = ['GET'=>[], 'POST'=>[], 'PUT'=>[], 'DELETE'=>[]];
    private array $globalMiddleware = [];

    public function middleware(callable $mw): void
    {
        $this->globalMiddleware[] = $mw;
    }

    public function add(string $method, string $pattern, $handler, array $middleware = []): void
    {
        $regex = $this->toRegex($pattern);
        $this->routes[strtoupper($method)][] = compact('pattern', 'regex', 'handler', 'middleware');
    }

    public function get($p,$h,$m=[]){ $this->add('GET',$p,$h,$m); }
    public function post($p,$h,$m=[]){ $this->add('POST',$p,$h,$m); }
    public function put($p,$h,$m=[]){ $this->add('PUT',$p,$h,$m); }
    public function delete($p,$h,$m=[]){ $this->add('DELETE',$p,$h,$m); }

    private function toRegex(string $pattern): string
    {
        // Convert /pages/{slug} to regex
        $regex = preg_replace('#\{[^/]+\}#', '([^/]+)', $pattern);
        return '#^' . rtrim($regex, '/') . '/?$#';
    }

    public function dispatch(Request $req): Response
    {
        $method = strtoupper($req->method);
        $path   = $req->path();

        // ÇÖZÜCÜ: Rotayı bulup çalıştırır (eşleşmezse 404 döner).
        $resolver = function(Request $req) use ($method, $path): Response {
            foreach ($this->routes[$method] ?? [] as $route) {
                if (preg_match($route['regex'], $path, $m)) {
                    array_shift($m);
                    $params = $m;

                    $handler = $route['handler'];

                    // Route-specific middleware pipeline
                    $routePipeline = $route['middleware'] ?? [];
                    $runner = array_reduce(array_reverse($routePipeline), function($next, $mw){
                        return function(Request $req) use ($mw, $next){
                            return $mw($req, $next);
                        };
                    }, function(Request $req) use ($handler, $params) {
                        if (is_array($handler) && is_string($handler[0])) {
                            $obj = new $handler[0];
                            $method = $handler[1];
                            $result = $obj->$method(...$params);
                        } elseif (is_callable($handler)) {
                            $result = $handler(...$params);
                        } else {
                            throw new \RuntimeException("Invalid route handler");
                        }
                        if ($result instanceof Response) return $result;
                        return new Response((string)$result);
                    });

                    return $runner($req);
                }
            }
            // Hiçbir rota eşleşmediyse 404
            return new Response('<h1>404 Not Found</h1>', 404);
        };

        // GLOBAL PIPELINE: Her istekte koşsun (redirect/normalizer burada çalışır)
        $globalRunner = array_reduce(array_reverse($this->globalMiddleware), function($next, $mw){
            return function(Request $req) use ($mw, $next){
                return $mw($req, $next);
            };
        }, $resolver);

        return $globalRunner($req);
    }
}
