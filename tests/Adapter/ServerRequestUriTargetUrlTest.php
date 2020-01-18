<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class ServerRequestUriTargetUrlTest extends TestCase
{
    public function testGetUriDefault(): void
    {
        $uri = $this->createRequest(null)->getUri();
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame('http://localhost/', $uri->__toString());
    }

    public function testGetUri(): void
    {
        $uri = $this->createRequest('https://example.com:1337/foo/bar?q=bar&a=42#fragment')->getUri();
        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame('https://example.com:1337/foo/bar?q=bar&a=42#fragment', $uri->__toString());
    }

    public function testGetRequestTargetDefaultMock(): void
    {
        $requestTarget = $this->createRequest(null)->getRequestTarget();
        $this->assertSame('/', $requestTarget);
    }

    public function testGetRequestTargetDefaultEmpty(): void
    {
        $request = $this->createRequest('');
        $this->assertSame('/', $request->getRequestTarget());;
    }

    public function testGetRequestTarget(): void
    {
        $requestTarget = $this->createRequest('https://example.com:1337/foo/bar?q=baz&a=42#fragment')->getRequestTarget();
        $this->assertSame('/foo/bar?q=baz&a=42', $requestTarget);
    }

    /**
     * TODO cover and activate!
     */
    public function ItFailsWithUri(): void
    {
        $request = $this->createRequest();
        $uriMock = $this->createMock(UriInterface::class);
        $this->expectException(\LogicException::class);
        // $request->withUri($uriMock);
    }

    /**
     *
     * @param string|null $uri
     * @param string|null $method
     *
     * @return Request
     */
    private function createRequest(
        ?string $uri = null,
        ?string $method = 'GET'
    ): Request {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare($method, [], [], [], [], [], null, $uri);

        return Request::fromSfWebRequest($symfonyRequestMock);
    }
}
