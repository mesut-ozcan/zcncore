<?php
use Core\Request;
use Core\Response;
use Core\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testSimpleGetRoute(): void
    {
        $r = new Router();
        $r->get('/hello/{name}', function(Request $req, $name) {
            return new Response("Hi {$name}", 200);
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/hello/ZCN';
        $req = Request::capture();

        $resp = $r->dispatch($req);
        $this->assertInstanceOf(Response::class, $resp);
        $this->assertSame(200, $this->getStatus($resp));
        $this->expectOutputString('Hi ZCN');
        $resp->send();
    }

    private function getStatus(Response $resp): int
    {
        $ref = new ReflectionClass($resp);
        $prop = $ref->getProperty('status');
        $prop->setAccessible(true);
        return $prop->getValue($resp);
    }
}