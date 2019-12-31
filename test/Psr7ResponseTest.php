<?php

/**
 * @see       https://github.com/laminas/laminas-psr7bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-psr7bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-psr7bridge/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Psr7Bridge;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Http\Response as LaminasResponse;
use Laminas\Psr7Bridge\Psr7Response;
use PHPUnit_Framework_TestCase as TestCase;

class Psr7ResponseTest extends TestCase
{
    public function getResponseData()
    {
        return [
            [ 'Test!', 200, [ 'Content-Type' => [ 'text/html' ] ] ],
            [ '', 204, [] ],
            [ 'Test!', 200, [
                'Content-Type'   => [ 'text/html; charset=utf-8' ],
                'Content-Length' => [ '5' ]
            ]],
            [ 'Test!', 202, [
                'Content-Type'   => [ 'text/html; level=1', 'text/html' ],
                'Content-Length' => [ '5' ]
            ]],
        ];
    }

    /**
     * @dataProvider getResponseData
     */
    public function testResponseToLaminas($body, $status, $headers)
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($body);

        $psr7Response = new Response($stream, $status, $headers);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $psr7Response);

        $laminasResponse = Psr7Response::toLaminas($psr7Response);
        $this->assertInstanceOf('Laminas\Http\Response', $laminasResponse);
        $this->assertEquals($body, (string) $laminasResponse->getBody());
        $this->assertEquals($status, $laminasResponse->getStatusCode());

        $laminasHeaders = $laminasResponse->getHeaders()->toArray();
        foreach ($headers as $type => $values) {
            foreach ($values as $value) {
                $this->assertContains($value, $laminasHeaders[$type]);
            }
        }
    }

    public function getResponseString()
    {
        return [
            [ "HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\n\r\nTest!" ],
            [ "HTTP/1.1 204 OK\r\n\r\n" ],
            [ "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\nContent-Length: 5\r\n\r\nTest!" ],
            [ "HTTP/1.1 200 OK\r\nContent-Type: text/html, text/xml\r\nContent-Length: 5\r\n\r\nTest!" ],
        ];
    }

    /**
     * @dataProvider getResponseString
     */
    public function testResponseFromLaminas($response)
    {
        $laminasResponse = LaminasResponse::fromString($response);
        $this->assertInstanceOf('Laminas\Http\Response', $laminasResponse);
        $psr7Response = Psr7Response::fromLaminas($laminasResponse);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $psr7Response);
        $this->assertEquals((string) $psr7Response->getBody(), $laminasResponse->getBody());
        $this->assertEquals($psr7Response->getStatusCode(), $laminasResponse->getStatusCode());

        $laminasHeaders = $laminasResponse->getHeaders()->toArray();
        foreach ($psr7Response->getHeaders() as $type => $values) {
            foreach ($values as $value) {
                $this->assertContains($value, $laminasHeaders[$type]);
            }
        }
    }
}
