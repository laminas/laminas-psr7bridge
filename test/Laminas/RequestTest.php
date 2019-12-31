<?php

/**
 * @see       https://github.com/laminas/laminas-psr7bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-psr7bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-psr7bridge/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Psr7Bridge\Laminas;

use Laminas\Psr7Bridge\Laminas\Request;
use PHPUnit_Framework_TestCase as TestCase;

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
        $this->assertInstanceOf('Laminas\Uri\Http', $request->getUri());
        $this->assertSame($path, $request->getUri()->getPath());
        $this->assertEmpty($request->getHeaders());
        $this->assertEmpty($request->getCookie());
        $this->assertEmpty($request->getQuery());
        $this->assertEmpty($request->getPost());
        $this->assertEmpty($request->getFiles());
    }
}
