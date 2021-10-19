<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
final class ServerRequestUriTargetUrlTest extends TestCase
{
    public function testGetUriDefault(): void
    {
        $uri = $this->createRequest(null)->getUri();
        static::assertInstanceOf(UriInterface::class, $uri);
        static::assertSame('http://localhost/', $uri->__toString());
    }

    public function testGetUri(): void
    {
        $uri = $this->createRequest('https://example.com:1337/foo/bar?q=bar&a=42#fragment')->getUri();
        static::assertInstanceOf(UriInterface::class, $uri);
        static::assertSame('https://example.com:1337/foo/bar?q=bar&a=42#fragment', $uri->__toString());
    }

    public function testGetRequestTargetDefaultMock(): void
    {
        $requestTarget = $this->createRequest(null)->getRequestTarget();
        static::assertSame('/', $requestTarget);
    }

    public function testGetRequestTargetDefaultEmpty(): void
    {
        $request = $this->createRequest('');
        static::assertSame('/', $request->getRequestTarget());
    }

    public function testGetRequestTarget(): void
    {
        $requestTarget = $this->createRequest('https://example.com:1337/foo/bar?q=baz&a=42#fragment')->getRequestTarget();
        static::assertSame('/foo/bar?q=baz&a=42', $requestTarget);
    }

    public function testItFailsWithUri(): void
    {
        $request = $this->createRequest();
        $uriMock = $this->createMock(UriInterface::class);
        $this->expectException(\LogicException::class);
        // when using sfWebRequest Adaption, when cannot change the URI or Host
        $request->withUri($uriMock);
    }

    private function createRequest(
        ?string $uri = null,
        string $method = 'GET'
    ): Request {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare($method, [], [], [], [], [], null, $uri);

        return Request::fromSfWebRequest($symfonyRequestMock);
    }
}
