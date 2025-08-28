<?php
use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase
{
    public function testEnvHelper(): void
    {
        $val = env('APP_ENV', 'local');
        $this->assertNotEmpty($val);
    }
}