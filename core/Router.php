<?php
namespace Core;

class Router
{
    private array $routes = ['GET'=>[], 'POST'=>[], 'PUT'=>[], 'DELETE'=>[]];
    private array $globalMiddleware = [];
    private array $mwAliases = []; // name => callable

    /** @var array<string, array{method:string,pattern:string,handler:mixed,middleware:array,regex:string,paramNames:array}> */
    private array $named = [];

    public function alias(string $name, callable $mw): void
    {
        $this->mwAliases[$name] = $mw;
    }

    public function middleware(callable $mw): void
    {
        $this->globalMiddleware[] = $mw;
    }

    public function add(string $method, string $pattern, $handler, array $middleware = [], ?string $name = null): void
    {
        [$regex, $paramNames] = $this->toRegex($pattern);
        $item = compact('pattern','regex','handler','middleware') + ['method'=>strtoupper($method), 'paramNames'=>$paramNames];
        $this->routes[strtoupper($method)][] = $item;
        if ($name) $this->named[$name] = $item;
    }

    // BC: eski imzayı koruyoruz
    public function get($p,$h,$m=[]){ $this->add('GET',$p,$h,$m,null); }
    public function post($p,$h,$m=[]){ $this->add('POST',$p,$h,$m,null); }
    public function put($p,$h,$m=[]){ $this->add('PUT',$p,$h,$m,null); }
    public function delete($p,$h,$m=[]){ $this->add('DELETE',$p,$h,$m,null); }

    // Named versiyonlar
    public function getNamed(string $name, string $p, $h, array $m = []): void { $this->add('GET', $p, $h, $m, $name); }
    public function postNamed(string $name, string $p, $h, array $m = []): void { $this->add('POST', $p, $h, $m, $name); }
    public function putNamed(string $name, string $p, $h, array $m = []): void { $this->add('PUT', $p, $h, $m, $name); }
    public function deleteNamed(string $name, string $p, $h, array $m = []): void { $this->add('DELETE', $p, $h, $m, $name); }

    private function toRegex(string $pattern): array
    {
        // {param} -> (?P<param>[^/]+)
        $paramNames = [];
        $regex = preg_replace_callback('#\{([^/]+)\}#', function($m) use (&$paramNames){
            $paramNames[] = $m[1];
            return '(?P<'.$m[1].'>[^/]+)';
        }, $pattern);
        $regex = '#^' . rtrim($regex, '/') . '/?$#';
        return [$regex, $paramNames];
    }

    private function resolveMiddlewareList(array $list): array
    {
        $out = [];
        foreach ($list as $mw) {
            if (is_string($mw) && isset($this->mwAliases[$mw])) {
                $out[] = $this->mwAliases[$mw];
            } elseif (is_callable($mw)) {
                $out[] = $mw;
            }
        }
        return $out;
    }

    /** Named route için URL üretir. */
    public function urlFor(string $name, array $params = [], bool $absolute = false): string
    {
        if (!isset($this->named[$name])) {
            throw new \InvalidArgumentException("Route not found: $name");
        }
        $pattern = $this->named[$name]['pattern'];
        $url = $pattern;
        foreach ($params as $k=>$v) {
            $url = str_replace('{'.$k.'}', rawurlencode((string)$v), $url);
        }
        // Eksik param varsa basit güvenlik: temizlemeden önce kontrol
        if (preg_match('#\{[^/]+\}#', $url)) {
            throw new \InvalidArgumentException("Missing parameters for route '$name'");
        }

        if ($absolute) {
            $req = Request::current();
            if ($req) {
                return $req->scheme().'://'.$req->host().$url;
            }
            $base = rtrim(Config::get('app.url',''), '/');
            return $base . $url;
        }
        return $url;
    }

    public function dispatch(Request $req): Response
    {
        $method = strtoupper($req->method);
        $path   = $req->path();

        $resolver = function(Request $req) use ($method, $path): Response {
            foreach ($this->routes[$method] ?? [] as $route) {
                if (preg_match($route['regex'], $path, $m)) {
                    array_shift($m);
                    // named captures
                    $args = [];
                    if (!empty($route['paramNames'])) {
                        foreach ($route['paramNames'] as $pn) {
                            if (isset($m[$pn])) $args[] = $m[$pn];
                        }
                    } else {
                        // fallback positional
                        foreach ($m as $k=>$v) if (is_int($k)) $args[] = $v;
                    }

                    $handler = $route['handler'];

                    $routePipeline = $this->resolveMiddlewareList($route['middleware'] ?? []);
                    $runner = array_reduce(array_reverse($routePipeline), function($next, $mw){
                        return function(Request $req) use ($mw, $next){
                            return $mw($req, $next);
                        };
                    }, function(Request $req) use ($handler, $args) {
                        if (is_array($handler) && is_string($handler[0])) {
                            $obj = new $handler[0];
                            $method = $handler[1];
                            $result = $obj->$method(...$args);
                        } elseif (is_callable($handler)) {
                            $result = $handler(...$args);
                        } else {
                            throw new \RuntimeException("Invalid route handler");
                        }
                        if ($result instanceof Response) return $result;
                        return new Response((string)$result);
                    });

                    return $runner($req);
                }
            }
            return new Response('<h1>404 Not Found</h1>', 404);
        };

        $globalRunner = array_reduce(array_reverse($this->globalMiddleware), function($next, $mw){
            return function(Request $req) use ($mw, $next){
                return $mw($req, $next);
            };
        }, $resolver);

        return $globalRunner($req);
    }
}
