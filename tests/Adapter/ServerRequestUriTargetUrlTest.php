<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 *
 * @covers \brnc\Symfony1\Message\Adapter\Request
 *
 * @uses   \sfWebRequest
 */
final class ServerRequestUriTargetUrlTest extends TestCase
{
    public function testGetUriDefault(): void
    {
        $uri = $this->createRequest(null)->getUri();
        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame('http://localhost/', $uri->__toString());
    }

    public function testGetUri(): void
    {
        $uri = $this->createRequest('https://example.com:1337/foo/bar?q=bar&a=42#fragment')->getUri();
        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame('https://example.com:1337/foo/bar?q=bar&a=42#fragment', $uri->__toString());
    }

    public function testGetRequestTargetDefaultMock(): void
    {
        $requestTarget = $this->createRequest(null)->getRequestTarget();
        self::assertSame('/', $requestTarget);
    }

    public function testGetRequestTargetDefaultEmpty(): void
    {
        $request = $this->createRequest('');
        self::assertSame('/', $request->getRequestTarget());
    }

    public function testGetRequestTarget(): void
    {
        $requestTarget = $this->createRequest('https://example.com:1337/foo/bar?q=baz&a=42#fragment')->getRequestTarget();
        self::assertSame('/foo/bar?q=baz&a=42', $requestTarget);
    }

    /** @covers \brnc\Symfony1\Message\Exception\LogicException */
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
