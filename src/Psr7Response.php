<?php

/**
 * @see       https://github.com/laminas/laminas-psr7bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-psr7bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-psr7bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Psr7Bridge;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Http\Response as LaminasResponse;
use Psr\Http\Message\ResponseInterface;

final class Psr7Response
{
    /**
     * Convert a PSR-7 response in a Laminas\Http\Response
     *
     * @param  ResponseInterface $psr7Response
     * @return LaminasResponse
     */
    public static function toLaminas(ResponseInterface $psr7Response)
    {
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

    /**
     * Convert a Laminas\Http\Response in a PSR-7 response, using laminas-diactoros
     *
     * @param  LaminasResponse $laminasResponse
     * @return Response
     */
    public static function fromLaminas(LaminasResponse $laminasResponse)
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
     * @param ResponseInterface $psr7Response
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
    public static function toZend(ResponseInterface $psr7Response)
    {
        return self::toLaminas(...func_get_args());
    }

    /**
     * @deprecated Use self::fromLaminas instead
     */
    public static function fromZend(LaminasResponse $laminasResponse)
    {
        return self::fromLaminas(...func_get_args());
    }
}
