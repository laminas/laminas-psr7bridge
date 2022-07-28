<?php

namespace Laminas\Psr7Bridge;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Http\Headers;
use Laminas\Http\Response as LaminasResponse;
use Psr\Http\Message\ResponseInterface;

use function fopen;
use function func_get_args;
use function implode;
use function sprintf;

final class Psr7Response
{
    public const URI_TEMP   = 'php://temp';
    public const URI_MEMORY = 'php://memory';

    /**
     * Convert a PSR-7 response in a Laminas\Http\Response
     */
    public static function toLaminas(ResponseInterface $psr7Response): LaminasResponse
    {
        $uri = $psr7Response->getBody()->getMetadata('uri');

        if ($uri === static::URI_TEMP || $uri === static::URI_MEMORY) {
            $response = sprintf(
                "HTTP/%s %d %s\r\n%s\r\n%s",
                $psr7Response->getProtocolVersion(),
                $psr7Response->getStatusCode(),
                $psr7Response->getReasonPhrase(),
                self::psr7HeadersToString($psr7Response),
                (string) $psr7Response->getBody()
            );

            return LaminasResponse::fromString($response);
        }

        $response       = new LaminasResponse\Stream();
        $laminasHeaders = Headers::fromString(self::psr7HeadersToString($psr7Response));
        $response->setStatusCode($psr7Response->getStatusCode());
        $response->setHeaders($laminasHeaders);
        $response->setStream(fopen($uri, 'rb'));

        return $response;
    }

    /**
     * Convert a Laminas\Http\Response in a PSR-7 response, using laminas-diactoros
     */
    public static function fromLaminas(LaminasResponse $laminasResponse): Response
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($laminasResponse->getBody());

        return new Response(
            $body,
            $laminasResponse->getStatusCode(),
            $laminasResponse->getHeaders()->toArray()
        );
    }

    /**
     * Convert the PSR-7 headers to string
     *
     * @return string
     */
    private static function psr7HeadersToString(ResponseInterface $psr7Response)
    {
        $headers = '';
        foreach ($psr7Response->getHeaders() as $name => $value) {
            $headers .= $name . ": " . implode(", ", $value) . "\r\n";
        }

        return $headers;
    }

    /**
     * Do not allow instantiation.
     */
    private function __construct()
    {
    }

    /**
     * @deprecated Use self::toLaminas instead
     */
    public static function toZend(ResponseInterface $psr7Response): LaminasResponse
    {
        return self::toLaminas(...func_get_args());
    }

    /**
     * @deprecated Use self::fromLaminas instead
     */
    public static function fromZend(LaminasResponse $laminasResponse): Response
    {
        return self::fromLaminas(...func_get_args());
    }
}
