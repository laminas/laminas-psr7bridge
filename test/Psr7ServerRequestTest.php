<?php

/**
 * @see       https://github.com/laminas/laminas-psr7bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-psr7bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-psr7bridge/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Psr7Bridge;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\UploadedFile;
use Laminas\Psr7Bridge\Psr7ServerRequest;
use PHPUnit_Framework_TestCase as TestCase;

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
}
