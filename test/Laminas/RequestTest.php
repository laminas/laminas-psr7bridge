<?php

declare(strict_types=1);

namespace LaminasTest\Psr7Bridge\Laminas;

use Laminas\Psr7Bridge\Laminas\Request;
use Laminas\Uri\Http as Uri;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testConstructor()
    {
        $method  = 'GET';
        $path    = '/foo';
        $request = new Request($method, $path, [], [], [], [], [], []);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($path, $request->getRequestUri());
        $this->assertInstanceOf(Uri::class, $request->getUri());
        $this->assertSame($path, $request->getUri()->getPath());
        $this->assertEmpty($request->getHeaders());
        $this->assertEmpty($request->getCookie());
        $this->assertEmpty($request->getQuery());
        $this->assertEmpty($request->getPost());
        $this->assertEmpty($request->getFiles());
    }
}
