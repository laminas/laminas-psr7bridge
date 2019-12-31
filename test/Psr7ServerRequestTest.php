<?php

/**
 * @see       https://github.com/laminas/laminas-psr7bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-psr7bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-psr7bridge/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Psr7Bridge;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\UploadedFile;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Request as LaminasRequest;
use Laminas\Psr7Bridge\Psr7ServerRequest;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\UploadedFileInterface;

class Psr7ServerRequestTest extends TestCase
{
    public function testToLaminasWithShallowOmitsBody()
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

        $uri = 'https://example.com/foo/bar?baz=bat';

        $method = 'PATCH';

        $body = fopen(__FILE__, 'r');

        $headers = [
            'Host'         => [ 'example.com' ],
            'X-Foo'        => [ 'bar' ],
            'Content-Type' => [ 'multipart/form-data' ],
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
        $this->assertInstanceOf('Laminas\Http\PhpEnvironment\Request', $laminasRequest);
        $this->assertInstanceOf('Laminas\Http\Request', $laminasRequest);

        // But, more specifically, an instance where we do not use superglobals
        // to inject it
        $this->assertInstanceOf('Laminas\Psr7Bridge\Laminas\Request', $laminasRequest);

        // Assert shallow conditions
        // (content, files, and body parameters are not injected)
        $this->assertEmpty($laminasRequest->getContent());
        $this->assertCount(0, $laminasRequest->getFiles());
        $this->assertCount(0, $laminasRequest->getPost());

        // Assert all other Request metadata
        $this->assertEquals($uri, $laminasRequest->getRequestUri());
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
        $this->assertInstanceOf('Laminas\Http\Header\Cookie', $cookie);
        $this->assertTrue(isset($cookie['PHPSESSID']));
        $this->assertEquals($cookies['PHPSESSID'], $cookie['PHPSESSID']);

        $test = $laminasRequest->getServer();
        $this->assertCount(2, $test);
        $this->assertEquals(__FILE__, $test->get('SCRIPT_NAME'));
        $this->assertEquals(__FILE__, $test->get('SCRIPT_FILENAME'));
    }

    public function testCanCastFullRequestToLaminas()
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

        $uri = 'https://example.com/foo/bar?baz=bat';

        $method = 'PATCH';

        $body = fopen(__FILE__, 'r');

        $headers = [
            'Host'         => [ 'example.com' ],
            'X-Foo'        => [ 'bar' ],
            'Content-Type' => [ 'multipart/form-data' ],
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
        $this->assertInstanceOf('Laminas\Http\PhpEnvironment\Request', $laminasRequest);
        $this->assertInstanceOf('Laminas\Http\Request', $laminasRequest);

        // But, more specifically, an instance where we do not use superglobals
        // to inject it
        $this->assertInstanceOf('Laminas\Psr7Bridge\Laminas\Request', $laminasRequest);

        $this->assertEquals($uri, $laminasRequest->getRequestUri());
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
        $this->assertInstanceOf('Laminas\Http\Header\Cookie', $cookie);
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

    public function testNestedFileParametersArePassedCorrectlyToLaminasRequest()
    {
        $this->markTestIncomplete('Functionality is written but untested');
    }

    public function testCustomHttpMethodsDoNotRaiseAnExceptionDuringConversionToLaminasRequest()
    {
        $this->markTestIncomplete('Functionality is written but untested');
    }

    public function getResponseData()
    {
        return [
            [
                'https://getlaminas.org/', // uri
                'GET', // http method
                [ 'Content-Type' => 'text/html' ], // headers
                '<html></html>', // body
                [ 'foo' => 'bar' ], // query params
                [], // post
                [], // files
            ],
            [
                'https://getlaminas.org/', // uri
                'POST', // http method
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Cookie' => sprintf("PHPSESSID=%s;foo=bar", uniqid())
                ], // headers
                '', // body
                [ 'foo' => 'bar' ], // query params
                [ 'baz' => 'bar' ], // post
                [], // files
            ],
            [
                'https://getlaminas.org/', // uri
                'POST', // http method
                [ 'Content-Type' => 'multipart/form-data' ], // headers
                file_get_contents(__FILE__), // body
                [ 'foo' => 'bar' ], // query params
                [], // post
                [
                    'file' => [
                        'test1' => [
                            'name' => 'test1.txt',
                            'type' => 'text/plain',
                            'tmp_name' => __FILE__,
                            'error' => 0,
                            'size' => 1,
                        ],
                        'test2' => [
                            'name' => 'test2.txt',
                            'type' => 'text/plain',
                            'tmp_name' => __FILE__,
                            'error' => 0,
                            'size' => 1,
                        ]
                    ]
                ], // files
            ],
            [
                'https://getlaminas.org/', // uri
                'POST', // http method
                [ 'Content-Type' => 'multipart/form-data' ], // headers
                file_get_contents(__FILE__), // body
                [ 'foo' => 'bar' ], // query params
                [], // post
                [
                    'file' => [
                        'name' => 'test2.txt',
                        'type' => 'text/plain',
                        'tmp_name' => __FILE__,
                        'error' => 0,
                        'size' => 1,
                    ]
                ], // files
            ]
        ];
    }

    /**
     * @dataProvider getResponseData
     */
    public function testFromLaminas($uri, $method, $headers, $body, $query, $post, $files)
    {
        $laminasRequest = new LaminasRequest();
        $laminasRequest->setUri($uri);
        $laminasRequest->setMethod($method);
        $laminasRequest->getHeaders()->addHeaders($headers);
        $laminasRequest->setContent($body);
        $laminasRequest->getQuery()->fromArray($query);
        $laminasRequest->getPost()->fromArray($post);
        $laminasRequest->getFiles()->fromArray($files);

        $psr7Request = Psr7ServerRequest::fromLaminas($laminasRequest);
        $this->assertInstanceOf('Laminas\Diactoros\ServerRequest', $psr7Request);
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

    private function compareUploadedFiles($laminas, $psr7)
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
        $request = new LaminasRequest();
        $laminasCookieData = ['foo' => 'test', 'bar' => 'test 2'];
        $request->getHeaders()->addHeader(new Cookie($laminasCookieData));

        $psr7Request = Psr7ServerRequest::fromLaminas($request);

        $psr7CookieData = $psr7Request->getCookieParams();

        $this->assertEquals(count($laminasCookieData), count($psr7CookieData));
        $this->assertEquals($laminasCookieData['foo'], $psr7CookieData['foo']);
        $this->assertEquals($laminasCookieData['bar'], $psr7CookieData['bar']);
    }
}
