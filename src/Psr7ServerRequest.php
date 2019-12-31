<?php

/**
 * @see       https://github.com/laminas/laminas-psr7bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-psr7bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-psr7bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Psr7Bridge;

use Psr\Http\Message\ServerRequestInterface;

final class Psr7ServerRequest
{
    /**
     * Convert a PSR-7 ServerRequest to a Laminas\Http server-side request.
     *
     * @param ServerRequestInterface $psr7Request
     * @param bool $shallow Whether or not to convert without body/file
     *     parameters; defaults to false, meaning a fully populated request
     *     is returned.
     * @return Laminas\Request
     */
    public static function toLaminas(ServerRequestInterface $psr7Request, $shallow = false)
    {
        if ($shallow) {
            return new Laminas\Request(
                $psr7Request->getMethod(),
                $psr7Request->getUri(),
                $psr7Request->getHeaders(),
                $psr7Request->getCookieParams(),
                $psr7Request->getQueryParams(),
                [],
                [],
                $psr7Request->getServerParams()
            );
        }

        $laminasRequest = new Laminas\Request(
            $psr7Request->getMethod(),
            $psr7Request->getUri(),
            $psr7Request->getHeaders(),
            $psr7Request->getCookieParams(),
            $psr7Request->getQueryParams(),
            $psr7Request->getParsedBody() ?: [],
            self::convertUploadedFiles($psr7Request->getUploadedFiles()),
            $psr7Request->getServerParams()
        );
        $laminasRequest->setContent($psr7Request->getBody());

        return $laminasRequest;
    }

    /**
     * Convert a PSR-7 uploaded files structure to a $_FILES structure
     *
     * @param \Psr\Http\Message\UploadedFileInterface[]
     * @return array
     */
    private static function convertUploadedFiles(array $uploadedFiles)
    {
        $files = [];
        foreach ($uploadedFiles as $name => $upload) {
            if (is_array($upload)) {
                $files[$name] = self::convertUploadedFiles($upload);
                continue;
            }

            $files[$name] = [
                'name'     => $upload->getClientFilename(),
                'type'     => $upload->getClientMediaType(),
                'size'     => $upload->getSize(),
                'tmp_name' => $upload->getStream(),
                'error'    => $upload->getError(),
            ];
        }
        return $files;
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
    public static function toZend(ServerRequestInterface $psr7Request, $shallow = false)
    {
        return self::toLaminas(...func_get_args());
    }
}
