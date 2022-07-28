<?php

namespace Laminas\Psr7Bridge;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Laminas\Http\PhpEnvironment\Request as LaminasPhpEnvironmentRequest;
use Laminas\Http\Request as LaminasRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

use function func_get_args;
use function is_array;
use function iterator_to_array;

use const UPLOAD_ERR_OK;

final class Psr7ServerRequest
{
    /**
     * Convert a PSR-7 ServerRequest to a Laminas\Http server-side request.
     *
     * @param bool $shallow Whether or not to convert without body/file
     *     parameters; defaults to false, meaning a fully populated request
     *     is returned.
     */
    public static function toLaminas(ServerRequestInterface $psr7Request, $shallow = false): Laminas\Request
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
     */
    public static function fromLaminas(LaminasRequest $laminasRequest): ServerRequest
    {
        $body = new Stream('php://memory', 'wb+');
        if ($laminasRequest->getContent() !== null) {
            $body->write($laminasRequest->getContent());
        }

        $headers = empty($laminasRequest->getHeaders()) ? [] : $laminasRequest->getHeaders()->toArray();
        $query   = empty($laminasRequest->getQuery()) ? [] : $laminasRequest->getQuery()->toArray();
        $post    = empty($laminasRequest->getPost()) ? [] : $laminasRequest->getPost()->toArray();
        $files   = empty($laminasRequest->getFiles()) ? [] : $laminasRequest->getFiles()->toArray();

        $request = new ServerRequest(
            $laminasRequest instanceof LaminasPhpEnvironmentRequest
                ? iterator_to_array($laminasRequest->getServer())
                : [],
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
     * @param UploadedFileInterface[] $uploadedFiles
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

            $uploadError   = $upload->getError();
            $isUploadError = $uploadError !== UPLOAD_ERR_OK;

            $files[$name] = [
                'name'     => $upload->getClientFilename(),
                'type'     => $upload->getClientMediaType(),
                'size'     => $upload->getSize(),
                'tmp_name' => ! $isUploadError ? $upload->getStream()->getMetadata('uri') : '',
                'error'    => $uploadError,
            ];
        }
        return $files;
    }

    /**
     * Convert a Laminas\Http file structure to PSR-7 uploaded files
     *
     * @param array $files
     * @return UploadedFile[]|UploadedFile
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
     *
     * @param bool $shallow
     */
    public static function toZend(ServerRequestInterface $psr7Request, $shallow = false): Laminas\Request
    {
        return self::toLaminas(...func_get_args());
    }

    /**
     * @deprecated Use self::fromLaminas instead
     */
    public static function fromZend(LaminasRequest $laminasRequest): ServerRequest
    {
        return self::fromLaminas(...func_get_args());
    }
}
