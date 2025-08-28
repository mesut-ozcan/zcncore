<?php
use Core\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testJsonHelper(): void
    {
        $resp = Response::json(['ok'=>true]);
        ob_start();
        $resp->send();
        $out = ob_get_clean();
        $this->assertStringContainsString('"ok":true', $out);
    }
}