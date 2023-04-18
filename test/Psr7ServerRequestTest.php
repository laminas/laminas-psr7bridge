<?php

declare(strict_types=1);

namespace LaminasTest\Psr7Bridge;

use Error;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\UploadedFile;
use Laminas\Http\Header\Cookie;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Request as LaminasRequest;
use Laminas\Psr7Bridge\Laminas\Request as BridgeRequest;
use Laminas\Psr7Bridge\Psr7ServerRequest;
use Laminas\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

use function basename;
use function count;
use function file_get_contents;
use function fopen;
use function is_array;
use function preg_replace;
use function sprintf;
use function uniqid;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

class Psr7ServerRequestTest extends TestCase
{
    public function testToLaminasWithShallowOmitsBody(): void
    {
        $server = [
            'SCRIPT_NAME'     => __FILE__,
            'SCRIPT_FILENAME' => __FILE__,
        ];

        $uploadedFiles = [
            'foo' => new UploadedFile(
                __FILE__,
                100,
                UPLOAD_ERR_OK,
                'foo.txt',
                'text/plain'
            ),
        ];

        $uri        = 'https://example.com/foo/bar?baz=bat';
        $requestUri = '/foo/bar?baz=bat';
        $method     = 'PATCH';

        $body = fopen(__FILE__, 'r');

        $headers = [
            'Host'         => ['example.com'],
            'X-Foo'        => ['bar'],
            'Content-Type' => ['multipart/form-data'],
        ];

        $cookies = [
            'PHPSESSID' => uniqid(),
        ];

        $bodyParams = [
            'foo' => 'bar',
        ];

        $psr7Request = (new ServerRequest(
            $server,
            $uploadedFiles,
            $uri,
            $method,
            $body,
            $headers
        ))
            ->withCookieParams($cookies)
            ->withParsedBody($bodyParams);

        $laminasRequest = Psr7ServerRequest::toLaminas($psr7Request, $shallow = true);

        // This needs to be a Laminas request
        $this->assertInstanceOf(Request::class, $laminasRequest);
        $this->assertInstanceOf(LaminasRequest::class, $laminasRequest);

        // But, more specifically, an instance where we do not use superglobals
        // to inject it
        $this->assertInstanceOf(BridgeRequest::class, $laminasRequest);

        // Assert shallow conditions
        // (content, files, and body parameters are not injected)
        $this->assertEmpty($laminasRequest->getContent());
        $this->assertCount(0, $laminasRequest->getFiles());
        $this->assertCount(0, $laminasRequest->getPost());

        // Assert all other Request metadata
        $this->assertEquals($requestUri, $laminasRequest->getRequestUri());
        $this->assertEquals($uri, $laminasRequest->getUri()->toString());
        $this->assertEquals($method, $laminasRequest->getMethod());

        $laminasHeaders = $laminasRequest->getHeaders();
        $this->assertTrue($laminasHeaders->has('Host'));
        $this->assertTrue($laminasHeaders->has('X-Foo'));
        $this->assertTrue($laminasHeaders->has('Content-Type'));
        $this->assertEquals('example.com', $laminasHeaders->get('Host')->getFieldValue());
        $this->assertEquals('bar', $laminasHeaders->get('X-Foo')->getFieldValue());
        $this->assertEquals('multipart/form-data', $laminasHeaders->get('Content-Type')->getFieldValue());

        $this->assertTrue($laminasHeaders->has('Cookie'));
        $cookie = $laminasHeaders->get('Cookie');
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertTrue(isset($cookie['PHPSESSID']));
        $this->assertEquals($cookies['PHPSESSID'], $cookie['PHPSESSID']);

        $test = $laminasRequest->getServer();
        $this->assertCount(2, $test);
        $this->assertEquals(__FILE__, $test->get('SCRIPT_NAME'));
        $this->assertEquals(__FILE__, $test->get('SCRIPT_FILENAME'));
    }

    public function testCanCastFullRequestToLaminas(): void
    {
        $server = [
            'SCRIPT_NAME'     => __FILE__,
            'SCRIPT_FILENAME' => __FILE__,
        ];

        $uploadedFiles = [
            'foo' => new UploadedFile(
                __FILE__,
                100,
                UPLOAD_ERR_OK,
                'foo.txt',
                'text/plain'
            ),
        ];

        $uri        = 'https://example.com/foo/bar?baz=bat';
        $requestUri = preg_replace('#^[^/:]+://[^/]+#', '', $uri);

        $method = 'PATCH';

        $body = fopen(__FILE__, 'r');

        $headers = [
            'Host'         => ['example.com'],
            'X-Foo'        => ['bar'],
            'Content-Type' => ['multipart/form-data'],
        ];

        $cookies = [
            'PHPSESSID' => uniqid(),
        ];

        $bodyParams = [
            'foo' => 'bar',
        ];

        $psr7Request = (new ServerRequest(
            $server,
            $uploadedFiles,
            $uri,
            $method,
            $body,
            $headers
        ))
            ->withCookieParams($cookies)
            ->withParsedBody($bodyParams);

        $laminasRequest = Psr7ServerRequest::toLaminas($psr7Request);

        // This needs to be a Laminas request
        $this->assertInstanceOf(Request::class, $laminasRequest);
        $this->assertInstanceOf(LaminasRequest::class, $laminasRequest);

        // But, more specifically, an instance where we do not use superglobals
        // to inject it
        $this->assertInstanceOf(BridgeRequest::class, $laminasRequest);

        $this->assertEquals($requestUri, $laminasRequest->getRequestUri());
        $this->assertEquals($uri, $laminasRequest->getUri()->toString());
        $this->assertEquals($method, $laminasRequest->getMethod());

        $laminasHeaders = $laminasRequest->getHeaders();
        $this->assertTrue($laminasHeaders->has('Host'));
        $this->assertTrue($laminasHeaders->has('X-Foo'));
        $this->assertTrue($laminasHeaders->has('Content-Type'));
        $this->assertEquals('example.com', $laminasHeaders->get('Host')->getFieldValue());
        $this->assertEquals('bar', $laminasHeaders->get('X-Foo')->getFieldValue());
        $this->assertEquals('multipart/form-data', $laminasHeaders->get('Content-Type')->getFieldValue());

        $this->assertTrue($laminasHeaders->has('Cookie'));
        $cookie = $laminasHeaders->get('Cookie');
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertTrue(isset($cookie['PHPSESSID']));
        $this->assertEquals($cookies['PHPSESSID'], $cookie['PHPSESSID']);

        $this->assertEquals(file_get_contents(__FILE__), (string) $laminasRequest->getContent());

        $test = $laminasRequest->getFiles();
        $this->assertCount(1, $test);
        $this->assertTrue(isset($test['foo']));
        $upload = $test->get('foo');
        $this->assertArrayHasKey('name', $upload);
        $this->assertArrayHasKey('type', $upload);
        $this->assertArrayHasKey('size', $upload);
        $this->assertArrayHasKey('tmp_name', $upload);
        $this->assertArrayHasKey('error', $upload);

        $this->assertEquals($bodyParams, $laminasRequest->getPost()->getArrayCopy());

        $test = $laminasRequest->getServer();
        $this->assertCount(2, $test);
        $this->assertEquals(__FILE__, $test->get('SCRIPT_NAME'));
        $this->assertEquals(__FILE__, $test->get('SCRIPT_FILENAME'));
    }

    public function testCanCastErroneousUploadToLaminasRequest(): void
    {
        $server = [
            'SCRIPT_NAME'     => __FILE__,
            'SCRIPT_FILENAME' => __FILE__,
        ];

        $uploadedFiles = [
            'foo' => new UploadedFile(
                __FILE__,
                0,
                UPLOAD_ERR_NO_FILE,
                '',
                ''
            ),
        ];

        $uri        = 'https://example.com/foo/bar?baz=bat';
        $requestUri = preg_replace('#^[^/:]+://[^/]+#', '', $uri);

        $method = 'PATCH';

        $body = fopen(__FILE__, 'r');

        $headers = [
            'Host'         => ['example.com'],
            'X-Foo'        => ['bar'],
            'Content-Type' => ['multipart/form-data'],
        ];

        $cookies = [
            'PHPSESSID' => uniqid(),
        ];

        $bodyParams = [
            'foo' => 'bar',
        ];

        $psr7Request = (new ServerRequest(
            $server,
            $uploadedFiles,
            $uri,
            $method,
            $body,
            $headers
        ))
            ->withCookieParams($cookies)
            ->withParsedBody($bodyParams);

        $laminasRequest = Psr7ServerRequest::toLaminas($psr7Request);

        // This needs to be a Laminas request
        $this->assertInstanceOf(Request::class, $laminasRequest);
        $this->assertInstanceOf(LaminasRequest::class, $laminasRequest);

        // But, more specifically, an instance where we do not use superglobals
        // to inject it
        $this->assertInstanceOf(BridgeRequest::class, $laminasRequest);

        $this->assertEquals($requestUri, $laminasRequest->getRequestUri());
        $this->assertEquals($uri, $laminasRequest->getUri()->toString());
        $this->assertEquals($method, $laminasRequest->getMethod());

        $laminasHeaders = $laminasRequest->getHeaders();
        $this->assertTrue($laminasHeaders->has('Host'));
        $this->assertTrue($laminasHeaders->has('X-Foo'));
        $this->assertTrue($laminasHeaders->has('Content-Type'));
        $this->assertEquals('example.com', $laminasHeaders->get('Host')->getFieldValue());
        $this->assertEquals('bar', $laminasHeaders->get('X-Foo')->getFieldValue());
        $this->assertEquals('multipart/form-data', $laminasHeaders->get('Content-Type')->getFieldValue());

        $this->assertTrue($laminasHeaders->has('Cookie'));
        $cookie = $laminasHeaders->get('Cookie');
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertTrue(isset($cookie['PHPSESSID']));
        $this->assertEquals($cookies['PHPSESSID'], $cookie['PHPSESSID']);

        $this->assertEquals(file_get_contents(__FILE__), (string) $laminasRequest->getContent());

        $test = $laminasRequest->getFiles();
        $this->assertCount(1, $test);
        $this->assertTrue(isset($test['foo']));
        $upload = $test->get('foo');
        $this->assertArrayHasKey('name', $upload);
        $this->assertEquals($upload['name'], '');
        $this->assertArrayHasKey('type', $upload);
        $this->assertEquals($upload['type'], '');
        $this->assertArrayHasKey('size', $upload);
        $this->assertEquals($upload['size'], 0);
        $this->assertArrayHasKey('tmp_name', $upload);
        $this->assertEquals($upload['tmp_name'], '');
        $this->assertArrayHasKey('error', $upload);
        $this->assertEquals($upload['error'], UPLOAD_ERR_NO_FILE);

        $this->assertEquals($bodyParams, $laminasRequest->getPost()->getArrayCopy());

        $test = $laminasRequest->getServer();
        $this->assertCount(2, $test);
        $this->assertEquals(__FILE__, $test->get('SCRIPT_NAME'));
        $this->assertEquals(__FILE__, $test->get('SCRIPT_FILENAME'));
    }

    public function testNestedFileParametersArePassedCorrectlyToLaminasRequest(): void
    {
        $uploadedFiles = [
            'foo-bar' => [
                new UploadedFile(
                    __FILE__,
                    0,
                    UPLOAD_ERR_NO_FILE,
                    '',
                    ''
                ),
                new UploadedFile(
                    __FILE__,
                    123,
                    UPLOAD_ERR_OK,
                    basename(__FILE__),
                    'plain/text'
                ),
            ],
        ];

        $psr7Request = new ServerRequest([], $uploadedFiles);

        $laminasRequest = Psr7ServerRequest::toLaminas($psr7Request);

        // This needs to be a Laminas request
        $this->assertInstanceOf(Request::class, $laminasRequest);
        $this->assertInstanceOf(LaminasRequest::class, $laminasRequest);

        // But, more specifically, an instance where we do not use superglobals
        // to inject it
        $this->assertInstanceOf(BridgeRequest::class, $laminasRequest);

        $test = $laminasRequest->getFiles();
        $this->assertCount(1, $test);
        $this->assertTrue(isset($test['foo-bar']));
        $upload = $test->get('foo-bar');
        $this->assertCount(2, $upload);
        $this->assertTrue(isset($upload[0]));
        $this->assertTrue(isset($upload[1]));

        $this->assertArrayHasKey('name', $upload[0]);
        $this->assertEquals('', $upload[0]['name']);
        $this->assertArrayHasKey('type', $upload[0]);
        $this->assertEquals('', $upload[0]['type']);
        $this->assertArrayHasKey('size', $upload[0]);
        $this->assertEquals(0, $upload[0]['size']);
        $this->assertArrayHasKey('tmp_name', $upload[0]);
        $this->assertEquals('', $upload[0]['tmp_name']);
        $this->assertArrayHasKey('error', $upload[0]);
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $upload[0]['error']);

        $this->assertArrayHasKey('name', $upload[1]);
        $this->assertEquals(basename(__FILE__), $upload[1]['name']);
        $this->assertArrayHasKey('type', $upload[1]);
        $this->assertEquals('plain/text', $upload[1]['type']);
        $this->assertArrayHasKey('size', $upload[1]);
        $this->assertEquals(123, $upload[1]['size']);
        $this->assertArrayHasKey('tmp_name', $upload[1]);
        $this->assertEquals(__FILE__, $upload[1]['tmp_name']);
        $this->assertArrayHasKey('error', $upload[1]);
        $this->assertEquals(UPLOAD_ERR_OK, $upload[1]['error']);
    }

    public function testCustomHttpMethodsDoNotRaiseAnExceptionDuringConversionToLaminasRequest(): void
    {
        $psr7Request = new ServerRequest([], [], null, 'CUSTOM_METHOD');

        $laminasRequest = Psr7ServerRequest::toLaminas($psr7Request);
        $this->assertSame('CUSTOM_METHOD', $laminasRequest->getMethod());
    }

    /**
     * @return non-empty-list<array{
     *     non-empty-string,
     *     non-empty-string,
     *     array<non-empty-string, string>,
     *     string,
     *     array<non-empty-string, string>,
     *     mixed[],
     *     mixed[],
     * }>
     */
    public static function getResponseData(): array
    {
        return [
            [
                'https://getlaminas.org/', // uri
                'GET', // http method
                ['Content-Type' => 'text/html'], // headers
                '<html></html>', // body
                ['foo' => 'bar'], // query params
                [], // post
                [], // files
            ],
            [
                'https://getlaminas.org/', // uri
                'POST', // http method
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cookie'       => sprintf("PHPSESSID=%s; foo=bar", uniqid()),
                ], // headers
                '', // body
                ['foo' => 'bar'], // query params
                ['baz' => 'bar'], // post
                [], // files
            ],
            [
                'https://getlaminas.org/', // uri
                'POST', // http method
                ['Content-Type' => 'multipart/form-data'], // headers
                file_get_contents(__FILE__), // body
                ['foo' => 'bar'], // query params
                [], // post
                [
                    'file' => [
                        'test1' => [
                            'name'     => 'test1.txt',
                            'type'     => 'text/plain',
                            'tmp_name' => __FILE__,
                            'error'    => 0,
                            'size'     => 1,
                        ],
                        'test2' => [
                            'name'     => 'test2.txt',
                            'type'     => 'text/plain',
                            'tmp_name' => __FILE__,
                            'error'    => 0,
                            'size'     => 1,
                        ],
                    ],
                ], // files
            ],
            [
                'https://getlaminas.org/', // uri
                'POST', // http method
                ['Content-Type' => 'multipart/form-data'], // headers
                file_get_contents(__FILE__), // body
                ['foo' => 'bar'], // query params
                [], // post
                [
                    'file' => [
                        'name'     => 'test2.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => __FILE__,
                        'error'    => 0,
                        'size'     => 1,
                    ],
                ], // files
            ],
        ];
    }

    /**
     * @dataProvider getResponseData
     * @param array<non-empty-string, string> $headers
     * @param array<non-empty-string, string> $query
     * @param mixed[]                         $post
     * @param mixed[]                         $files
     */
    public function testFromLaminas(
        string $uri,
        string $method,
        array $headers,
        string $body,
        array $query,
        array $post,
        array $files
    ): void {
        $laminasRequest = new LaminasRequest();
        $laminasRequest->setUri($uri);
        $laminasRequest->setMethod($method);
        $laminasRequest->getHeaders()->addHeaders($headers);
        $laminasRequest->setContent($body);
        $laminasRequest->getQuery()->fromArray($query);
        $laminasRequest->getPost()->fromArray($post);
        $laminasRequest->getFiles()->fromArray($files);

        $psr7Request = Psr7ServerRequest::fromLaminas($laminasRequest);
        $this->assertInstanceOf(ServerRequest::class, $psr7Request);
        // URI
        $this->assertEquals($uri, (string) $psr7Request->getUri());
        // HTTP method
        $this->assertEquals($method, $psr7Request->getMethod());
        // headers
        $psr7Headers = $psr7Request->getHeaders();
        foreach ($headers as $key => $value) {
            $this->assertContains($value, $psr7Headers[$key]);
        }
        // body
        $this->assertEquals($body, (string) $psr7Request->getBody());
        // query params
        $this->assertEquals($query, $psr7Request->getQueryParams());
        // post
        $this->assertEquals($post, $psr7Request->getParsedBody());
        // files
        $this->compareUploadedFiles($files, $psr7Request->getUploadedFiles());
    }

    /**
     * @param mixed[]                     $laminas note: structure is nested/recursive/complex, therefore not well-typed
     * @param UploadedFileInterface|array $psr7
     */
    private function compareUploadedFiles($laminas, $psr7): void
    {
        if (! $psr7 instanceof UploadedFileInterface) {
            $this->assertEquals(count($laminas), count($psr7), 'number of files should be same');
        }

        foreach ($laminas as $name => $value) {
            if (is_array($value)) {
                $this->compareUploadedFiles($laminas[$name], $psr7[$name]);
                continue;
            }

            $this->assertEquals($laminas['name'], $psr7->getClientFilename());
            $this->assertEquals($laminas['type'], $psr7->getClientMediaType());
            $this->assertEquals($laminas['size'], $psr7->getSize());
            $this->assertEquals($laminas['tmp_name'], $psr7->getStream()->getMetadata('uri'));
            $this->assertEquals($laminas['error'], $psr7->getError());
            break;
        }
    }

    public function testFromLaminasConvertsCookies()
    {
        $request           = new LaminasRequest();
        $laminasCookieData = ['foo' => 'test', 'bar' => 'test 2'];
        $request->getHeaders()->addHeader(new Cookie($laminasCookieData));

        $psr7Request = Psr7ServerRequest::fromLaminas($request);

        $psr7CookieData = $psr7Request->getCookieParams();

        $this->assertEquals(count($laminasCookieData), count($psr7CookieData));
        $this->assertEquals($laminasCookieData['foo'], $psr7CookieData['foo']);
        $this->assertEquals($laminasCookieData['bar'], $psr7CookieData['bar']);
    }

    public function testServerParams()
    {
        $laminasRequest = new Request();
        $laminasRequest->setServer(new Parameters(['REMOTE_ADDR' => '127.0.0.1']));

        $psr7Request = Psr7ServerRequest::fromLaminas($laminasRequest);

        $params = $psr7Request->getServerParams();
        $this->assertArrayHasKey('REMOTE_ADDR', $params);
        $this->assertSame('127.0.0.1', $params['REMOTE_ADDR']);
    }

    /**
     * @see https://github.com/zendframework/zend-psr7bridge/issues/27
     */
    public function testBaseUrlFromGlobal()
    {
        $_SERVER = [
            'HTTP_HOST'       => 'host.com',
            'SERVER_PORT'     => '80',
            'REQUEST_URI'     => '/test/path/here?foo=bar',
            'SCRIPT_FILENAME' => '/c/root/test/path/here/index.php',
            'PHP_SELF'        => '/test/path/here/index.php',
            'SCRIPT_NAME'     => '/test/path/here/index.php',
            'QUERY_STRING'    => 'foo=bar',
        ];

        $psr7           = ServerRequestFactory::fromGlobals();
        $converted      = Psr7ServerRequest::toLaminas($psr7);
        $laminasRequest = new Request();

        $this->assertSame($laminasRequest->getBaseUrl(), $converted->getBaseUrl());
    }

    /**
     * @requires PHP 7
     */
    public function testPrivateConstruct()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage(sprintf('Call to private %s::__construct', Psr7ServerRequest::class));
        new Psr7ServerRequest();
    }

    public function testFromLaminasCanHandleNullContent()
    {
        $laminasRequest = new LaminasRequest();
        $laminasRequest->setContent(null);

        $psr7Request = Psr7ServerRequest::fromLaminas($laminasRequest);
        $this->assertEmpty($psr7Request->getBody()->getContents());
    }
}
