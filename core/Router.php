<?php
namespace Core;

use Core\Validation\FormRequest;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

class Router
{
    private array $routes = ['GET'=>[], 'POST'=>[], 'PUT'=>[], 'DELETE'=>[]];
    /** @var array<int, callable> */
    private array $globalMiddleware = [];
    /** @var array<string, callable|string> */
    private array $mwAliases = []; // name => callable|string (class-string)

    /**
     * @var array<string, array{
     *   method:string,pattern:string,handler:mixed,middleware:array,regex:string,paramNames:array
     * }>
     */
    private array $named = [];

    // Group stacks
    private array $prefixStack = [''];
    private array $mwStack = [[]];
    private array $namePrefixStack = [''];

    /**
     * Bir middleware alias'ı tanımlar.
     * @param callable|string $mw callable veya class-string ( __invoke )
     */
    public function alias(string $name, $mw): void
    {
        $this->mwAliases[$name] = $mw;
    }

    /**
     * Global middleware ekler. callable ya da class-string kabul eder.
     * @param callable|string $mw
     */
    public function middleware($mw): void
    {
        // alias adı gelebilir
        if (is_string($mw) && isset($this->mwAliases[$mw])) {
            $mw = $this->mwAliases[$mw];
        }
        // class-string ise örnekle
        if (is_string($mw) && class_exists($mw)) {
            $mw = new $mw();
        }
        if (is_callable($mw)) {
            $this->globalMiddleware[] = $mw;
        }
    }

    /**
     * Grup: prefix + middleware + name prefix
     * @param string $prefix
     * @param array<int, callable|string> $middleware
     * @param callable(\Core\Router):void $callback
     * @param string $namePrefix
     */
    public function group(string $prefix, array $middleware, callable $callback, string $namePrefix = ''): void
    {
        $prefix = '/' . ltrim($prefix, '/');
        $newPrefix = rtrim(end($this->prefixStack), '/') . $prefix;
        $this->prefixStack[] = $newPrefix;

        $resolved = $this->resolveMiddlewareList($middleware);
        $merged = array_merge(end($this->mwStack), $resolved);
        $this->mwStack[] = $merged;

        $this->namePrefixStack[] = end($this->namePrefixStack) . $namePrefix;

        $callback($this);

        array_pop($this->prefixStack);
        array_pop($this->mwStack);
        array_pop($this->namePrefixStack);
    }

    /**
     * Altyapı: bir rota kaydı ekler.
     * @param string $method
     * @param string $pattern
     * @param mixed  $handler
     * @param array<int, callable|string> $middleware
     * @param string|null $name
     */
    public function add(string $method, string $pattern, $handler, array $middleware = [], ?string $name = null): void
    {
        // group prefix
        $prefix = end($this->prefixStack);
        $pattern = rtrim($prefix, '/') . '/' . ltrim($pattern, '/');
        if ($pattern === '') $pattern = '/';

        // group middleware + route middleware
        $mw = array_merge(end($this->mwStack), $this->resolveMiddlewareList($middleware));

        [$regex, $paramNames] = $this->toRegex($pattern);
        $item = [
            'pattern'    => $pattern,
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => $mw,
            'method'     => strtoupper($method),
            'paramNames' => $paramNames,
        ];
        $this->routes[strtoupper($method)][] = $item;

        if ($name) {
            $fullName = end($this->namePrefixStack) . $name;
            $this->named[$fullName] = $item;
        }
    }

    // ----------------------------
    // Yardımcılar (GET/POST/PUT/DELETE)
    // Not: Geriye dönük uyumluluk için 3. parametre hem name(string) hem middleware(array) olabilir.
    // 4. parametre de buna göre name veya middleware olarak esnek yorumlanır.

    /**
     * @param string $p
     * @param mixed  $h
     * @param string|array|null $nameOrMw
     * @param string|array $maybeNameOrMw
     */
    public function get(string $p, $h, $nameOrMw = null, $maybeNameOrMw = []): void
    {
        [$name, $mw] = $this->normalizeNameAndMiddleware($nameOrMw, $maybeNameOrMw);
        $this->add('GET', $p, $h, $mw, $name);
    }

    public function post(string $p, $h, $nameOrMw = null, $maybeNameOrMw = []): void
    {
        [$name, $mw] = $this->normalizeNameAndMiddleware($nameOrMw, $maybeNameOrMw);
        $this->add('POST', $p, $h, $mw, $name);
    }

    public function put(string $p, $h, $nameOrMw = null, $maybeNameOrMw = []): void
    {
        [$name, $mw] = $this->normalizeNameAndMiddleware($nameOrMw, $maybeNameOrMw);
        $this->add('PUT', $p, $h, $mw, $name);
    }

    public function delete(string $p, $h, $nameOrMw = null, $maybeNameOrMw = []): void
    {
        [$name, $mw] = $this->normalizeNameAndMiddleware($nameOrMw, $maybeNameOrMw);
        $this->add('DELETE', $p, $h, $mw, $name);
    }

    // Named helpers
    public function getNamed(string $name, string $p, $h, array $m = []): void { $this->add('GET', $p, $h, $m, $name); }
    public function postNamed(string $name, string $p, $h, array $m = []): void { $this->add('POST', $p, $h, $m, $name); }
    public function putNamed(string $name, string $p, $h, array $m = []): void { $this->add('PUT', $p, $h, $m, $name); }
    public function deleteNamed(string $name, string $p, $h, array $m = []): void { $this->add('DELETE', $p, $h, $m, $name); }

    /**
     * @param string|array|null $a
     * @param string|array $b
     * @return array{0:?string,1:array}
     */
    private function normalizeNameAndMiddleware($a, $b): array
    {
        $name = null; $mw = [];
        if (is_string($a)) {
            // get('/p',h,'route.name', [...])
            $name = $a;
            $mw = is_array($b) ? $b : [];
        } elseif (is_array($a)) {
            // get('/p',h,[mw,...], 'route.name')
            $mw = $a;
            if (is_string($b)) $name = $b;
        } elseif ($a === null) {
            // get('/p',h)  veya get('/p',h,null,[mw])  veya get('/p',h,null,'route.name')
            if (is_array($b)) { $mw = $b; }
            elseif (is_string($b)) { $name = $b; }
        }
        return [$name, $mw];
    }

    // ----------------------------

    private function toRegex(string $pattern): array
    {
        $paramNames = [];
        $regex = preg_replace_callback('#\{([^/]+)\}#', function($m) use (&$paramNames){
            $paramNames[] = $m[1];
            return '(?P<'.$m[1].'>[^/]+)';
        }, $pattern);
        $regex = '#^' . rtrim($regex, '/') . '/?$#';
        return [$regex, $paramNames];
    }

    /**
     * @param array<int, callable|string> $list
     * @return array<int, callable>
     */
    private function resolveMiddlewareList(array $list): array
    {
        $out = [];
        foreach ($list as $mw) {
            if (is_string($mw)) {
                // alias ismi olabilir
                if (isset($this->mwAliases[$mw])) {
                    $mwAlias = $this->mwAliases[$mw];
                    if (is_string($mwAlias) && class_exists($mwAlias)) {
                        $out[] = new $mwAlias();
                    } elseif (is_callable($mwAlias)) {
                        $out[] = $mwAlias;
                    }
                } elseif (class_exists($mw)) {
                    // doğrudan class-string verilmiş
                    $out[] = new $mw();
                }
            } elseif (is_callable($mw)) {
                $out[] = $mw;
            }
        }
        return $out;
    }

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
        if (preg_match('#\{[^/]+\}#', $url)) {
            throw new \InvalidArgumentException("Missing parameters for route '$name'");
        }
        if ($absolute) {
            $req = Request::current();
            if ($req) return $req->scheme().'://'.$req->host().$url;
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
                    // named captures → args
                    $args = [];
                    if (!empty($route['paramNames'])) {
                        foreach ($route['paramNames'] as $pn) {
                            if (isset($m[$pn])) $args[] = $m[$pn];
                        }
                    } else {
                        foreach ($m as $k=>$v) if (is_int($k)) $args[] = $v;
                    }

                    $handler = $route['handler'];

                    // pipeline
                    $routePipeline = $route['middleware'] ?? [];
                    $runner = array_reduce(array_reverse($routePipeline), function($next, $mw){
                        return function(Request $req) use ($mw, $next){
                            return $mw($req, $next);
                        };
                    }, function(Request $req) use ($handler, $args) {
                        // ---- Handler çağrısı (FormRequest injection) ----
                        if (is_array($handler) && is_string($handler[0])) {
                            $obj = new $handler[0];
                            $method = $handler[1];

                            $ref = new ReflectionMethod($obj, $method);
                            $finalArgs = $this->resolveParameters($ref, $args);

                            $result = $obj->$method(...$finalArgs);
                        } elseif (is_callable($handler)) {
                            // Closure veya function
                            if (is_array($handler) && is_object($handler[0])) {
                                $ref = new ReflectionMethod($handler[0], $handler[1]);
                            } else {
                                $ref = new ReflectionFunction($handler);
                            }
                            $finalArgs = $this->resolveParameters($ref, $args);

                            $result = $handler(...$finalArgs);
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

    /**
     * @param \ReflectionMethod|\ReflectionFunction $ref
     * @param array<int, mixed> $routeArgs
     * @return array<int, mixed>
     */
    private function resolveParameters($ref, array $routeArgs): array
    {
        $params = $ref->getParameters();
        $out = [];
        $routeIdx = 0;

        foreach ($params as $p) {
            $type = $p->getType();
            if ($type instanceof ReflectionNamedType) {
                $t = $type->getName();

                // Request
                if ($t === Request::class || $t === '\\Core\\Request') {
                    $out[] = Request::current() ?: Request::capture();
                    continue;
                }

                // FormRequest subclass
                if (class_exists($t) && is_subclass_of($t, FormRequest::class)) {
                    /** @var FormRequest $fr */
                    $fr = new $t(Request::current() ?: Request::capture());
                    if ($fr->fails()) {
                        $wants = (Request::current()?->wantsJson()) ?? false;
                        if ($wants) {
                            (new \Core\Response(
                                json_encode(['ok'=>false,'errors'=>$fr->errors()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                                422,
                                ['Content-Type'=>'application/json; charset=UTF-8']
                            ))->send();
                            exit;
                        }
                        // HTML istek: old & errors set ve geri dön
                        \Core\Session::setOld((Request::current()?->all()) ?? []);
                        \Core\Session::setErrors($fr->errors());

                        $back = Request::current()?->server['HTTP_REFERER'] ?? (Request::current()?->path() ?? '/');
                        \Core\Response::redirect($back, 302)->send();
                        exit;
                    }
                    $out[] = $fr;
                    continue;
                }
            }

            // Tip yoksa veya farklı tipse → route argümanı tüket
            if (array_key_exists($routeIdx, $routeArgs)) {
                $out[] = $routeArgs[$routeIdx++];
            } elseif ($p->isDefaultValueAvailable()) {
                $out[] = $p->getDefaultValue();
            } else {
                $out[] = null;
            }
        }

        // Ekstra route arg varsa ekle (geriye dönük uyum)
        while (array_key_exists($routeIdx, $routeArgs)) {
            $out[] = $routeArgs[$routeIdx++];
        }

        return $out;
    }
}