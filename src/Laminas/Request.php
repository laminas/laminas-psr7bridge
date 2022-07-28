<?php

namespace Laminas\Psr7Bridge\Laminas;

use Laminas\Http\Header\Cookie;
use Laminas\Http\PhpEnvironment\Request as BaseRequest;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\UriInterface;

use function preg_replace;

class Request extends BaseRequest
{
    /**
     * This method overloads the original constructor to accept the various
     * request metadata instead of pulling from superglobals.
     *
     * @param string $method
     * @param string|UriInterface $uri
     * @param array $headers
     * @param array $cookies
     * @param array $queryStringArguments
     * @param array $postParameters
     * @param array $uploadedFiles
     * @param array $serverParams
     */
    public function __construct(
        $method,
        $uri,
        array $headers,
        array $cookies,
        array $queryStringArguments,
        array $postParameters,
        array $uploadedFiles,
        array $serverParams
    ) {
        $this->setAllowCustomMethods(true);

        $this->setMethod($method);
        // Remove the "http(s)://hostname" part from the URI
        $this->setRequestUri(preg_replace('#^[^/:]+://[^/]+#', '', (string) $uri));
        $this->setUri((string) $uri);

        $headerCollection = $this->getHeaders();
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $headerCollection->addHeaderLine($name, $value);
            }
        }

        if (! empty($cookies)) {
            $headerCollection->addHeader(new Cookie($cookies));
        }

        $this->setQuery(new Parameters($queryStringArguments));
        $this->setPost(new Parameters($postParameters));
        $this->setFiles(new Parameters($uploadedFiles));

        // Do not use `setServerParams()`, as that extracts headers, URI, etc.
        $this->serverParams = new Parameters($serverParams);
    }
}
