<?php

declare(strict_types=1);

namespace LaminasTest\Psr7Bridge;

use Error;
use Iterator;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Http\Header\SetCookie;
use Laminas\Http\Response as LaminasResponse;
use Laminas\Psr7Bridge\Psr7Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function tmpfile;

class Psr7ResponseTest extends TestCase
{
    /** @return non-empty-list<array{string, positive-int, array<string, list<string>>}> */
    public function getResponseData(): array
    {
        return [
            ['Test!', 200, ['Content-Type' => ['text/html']]],
            ['', 204, []],
            [
                'Test!',
                200,
                [
                    'Content-Type'   => ['text/html; charset=utf-8'],
                    'Content-Length' => ['5'],
                ],
            ],
            [
                'Test!',
                202,
                [
                    'Content-Type'   => ['text/html; level=1'],
                    'Content-Length' => ['5'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getResponseData
     */
    public function testResponseToLaminas(string $body, int $status, array $headers): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($body);

        $psr7Response = new Response($stream, $status, $headers);
        $this->assertInstanceOf(ResponseInterface::class, $psr7Response);

        $laminasResponse = Psr7Response::toLaminas($psr7Response);
        $this->assertInstanceOf(LaminasResponse::class, $laminasResponse);
        $this->assertSame($body, $laminasResponse->getBody());
        $this->assertEquals($status, $laminasResponse->getStatusCode());

        $laminasHeaders = $laminasResponse->getHeaders()->toArray();
        foreach ($headers as $type => $values) {
            foreach ($values as $value) {
                $this->assertStringContainsString($value, $laminasHeaders[$type]);
            }
        }
    }

    /**
     * @dataProvider getResponseData
     */
    public function testResponseToLaminasWithMemoryStream(string $body, int $status, array $headers): void
    {
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($body);

        $psr7Response = new Response($stream, $status, $headers);
        $this->assertInstanceOf(ResponseInterface::class, $psr7Response);

        $laminasResponse = Psr7Response::toLaminas($psr7Response);
        $this->assertInstanceOf(LaminasResponse::class, $laminasResponse);
        $this->assertSame($body, $laminasResponse->getBody());
        $this->assertEquals($status, $laminasResponse->getStatusCode());

        $laminasHeaders = $laminasResponse->getHeaders()->toArray();
        foreach ($headers as $type => $values) {
            foreach ($values as $value) {
                $this->assertStringContainsString($value, $laminasHeaders[$type]);
            }
        }
    }

    /**
     * @dataProvider getResponseData
     */
    public function testResponseToLaminasFromRealStream(string $body, int $status, array $headers): void
    {
        $stream = new Stream(tempnam(sys_get_temp_dir(), 'Test'), 'wb+');
        $stream->write($body);

        $psr7Response = new Response($stream, $status, $headers);
        $this->assertInstanceOf(ResponseInterface::class, $psr7Response);

        $laminasResponse = Psr7Response::toLaminas($psr7Response);
        $this->assertInstanceOf(LaminasResponse::class, $laminasResponse);
        $this->assertSame($body, $laminasResponse->getBody());
        $this->assertEquals($status, $laminasResponse->getStatusCode());

        $laminasHeaders = $laminasResponse->getHeaders()->toArray();
        foreach ($headers as $type => $values) {
            foreach ($values as $value) {
                $this->assertStringContainsString($value, $laminasHeaders[$type]);
            }
        }
    }

    /** @return non-empty-list<array{non-empty-string}> */
    public function getResponseString(): array
    {
        return [
            ["HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\n\r\nTest!"],
            ["HTTP/1.1 204 OK\r\n\r\n"],
            ["HTTP/1.1 200 OK\r\nContent-Type: text/html\r\nContent-Length: 5\r\n\r\nTest!"],
            ["HTTP/1.1 200 OK\r\nContent-Type: text/html, text/xml\r\nContent-Length: 5\r\n\r\nTest!"],
        ];
    }

    /**
     * @dataProvider getResponseString
     */
    public function testResponseFromLaminas(string $response): void
    {
        $laminasResponse = LaminasResponse::fromString($response);
        $this->assertInstanceOf(LaminasResponse::class, $laminasResponse);
        $psr7Response = Psr7Response::fromLaminas($laminasResponse);
        $this->assertInstanceOf(ResponseInterface::class, $psr7Response);
        $this->assertEquals((string) $psr7Response->getBody(), $laminasResponse->getBody());
        $this->assertEquals($psr7Response->getStatusCode(), $laminasResponse->getStatusCode());

        $laminasHeaders = $laminasResponse->getHeaders()->toArray();
        foreach ($psr7Response->getHeaders() as $type => $values) {
            foreach ($values as $value) {
                $this->assertStringContainsString($value, $laminasHeaders[$type]);
            }
        }
    }

    /**
     * @requires PHP 7
     */
    public function testPrivateConstruct()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage(sprintf('Call to private %s::__construct', Psr7Response::class));
        new Psr7Response();
    }

    public function testConvertedHeadersAreInstanceOfTheirAppropriateClasses()
    {
        $psr7Response    = (new Response(tmpfile()))->withAddedHeader('Set-Cookie', 'foo=bar;domain=.laminas.dev');
        $laminasResponse = Psr7Response::toLaminas($psr7Response);

        $cookies = $laminasResponse->getHeaders()->get('Set-Cookie');
        $this->assertInstanceOf(Iterator::class, $cookies);
        $this->assertCount(1, $cookies);
        /** @var SetCookie $cookie */
        $cookie = $cookies[0];
        $this->assertInstanceOf(SetCookie::class, $cookie);
        $this->assertSame('.laminas.dev', $cookie->getDomain());
    }
}
