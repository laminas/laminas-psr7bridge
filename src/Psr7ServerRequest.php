<?php

/**
 * @see       https://github.com/laminas/laminas-psr7bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-psr7bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-psr7bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Psr7Bridge;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Laminas\Http\Request as LaminasRequest;
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
     * Convert a Laminas\Http\Response in a PSR-7 response, using laminas-diactoros
     *
     * @param  LaminasRequest $laminasRequest
     * @return ServerRequest
     */
    public static function fromLaminas(LaminasRequest $laminasRequest)
    {
        $body = new Stream('php://memory', 'wb+');
        $body->write($laminasRequest->getContent());

        $headers = empty($laminasRequest->getHeaders()) ? [] : $laminasRequest->getHeaders()->toArray();
        $query   = empty($laminasRequest->getQuery()) ? [] : $laminasRequest->getQuery()->toArray();
        $post    = empty($laminasRequest->getPost()) ? [] : $laminasRequest->getPost()->toArray();
        $files   = empty($laminasRequest->getFiles()) ? [] : $laminasRequest->getFiles()->toArray();

        $request = new ServerRequest(
            [],
            self::convertFilesToUploaded($files),
            $laminasRequest->getUriString(),
            $laminasRequest->getMethod(),
            $body,
            $headers
        );
        $request = $request->withQueryParams($query);

        $cookie = $laminasRequest->getCookie();
        if (false !== $cookie) {
            $request = $request->withCookieParams($cookie->getArrayCopy());
        }

        return $request->withParsedBody($post);
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
                'tmp_name' => $upload->getStream()->getMetadata('uri'),
                'error'    => $upload->getError(),
            ];
        }
        return $files;
    }

    /**
     * Convert a Laminas\Http file structure to PSR-7 uploaded files
     *
     * @param array
     * @return UploadedFile[]
     */
    private static function convertFilesToUploaded(array $files)
    {
        $uploadedFiles = [];
        foreach ($files as $name => $value) {
            if (is_array($value)) {
                $uploadedFiles[$name] = self::convertFilesToUploaded($value);
                continue;
            }
            return new UploadedFile(
                $files['tmp_name'],
                $files['size'],
                $files['error'],
                $files['name'],
                $files['type']
            );
        }
        return $uploadedFiles;
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

    /**
     * @deprecated Use self::fromLaminas instead
     */
    public static function fromZend(LaminasRequest $laminasRequest)
    {
        return self::fromLaminas(...func_get_args());
    }
}
