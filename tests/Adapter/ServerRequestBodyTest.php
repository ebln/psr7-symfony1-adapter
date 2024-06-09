<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 *
 * @covers \brnc\Symfony1\Message\Adapter\Request
 *
 * @uses   \sfWebRequest
 */
final class ServerRequestBodyTest extends TestCase
{
    public function testConstructorGetBody(): void
    {
        $body = $this->createRequest('POST')->getBody();
        self::assertInstanceOf(StreamInterface::class, $body);
        self::assertSame('', $body->getContents(), 'Expected empty stream.');
        self::assertTrue($body->isReadable(), 'Default getBody() should be readable.');
        self::assertTrue($body->isWritable(), 'Default getBody() is writable as a quirk.');
    }

    public function testStaticConstructionGetBodyHasContent(): void
    {
        $body = $this->createRequest('POST', [], 'dummy content')->getBody();
        self::assertInstanceOf(StreamInterface::class, $body);
        self::assertSame('dummy content', $body->getContents(), 'Expected stream to have content.');
        self::assertTrue($body->isReadable(), 'Static constructed getBody() should be readable.');
        self::assertTrue($body->isWritable(), 'Static constructed getBody() is writable as a quirk.');
    }

    public function testStaticConstructionUsingPhpInputGetBody(): void
    {
        $body = $this->createRequest('POST', [Request::OPTION_BODY_USE_STREAM => true])->getBody();
        self::assertInstanceOf(StreamInterface::class, $body);
        self::assertSame('', $body->getContents(), 'Expected empty stream.');
        self::assertTrue($body->isReadable(), 'Static constructed getBody() should be readable.');
        self::assertTrue($body->isWritable(), 'Static constructed  getBody() is writable as a quirk.');
    }

    public function testWithBody(): void
    {
        $request         = $this->createRequest();
        $streamMock      = $this->createMock(StreamInterface::class);
        $requestWithBody = $request->withBody($streamMock);
        self::assertSame(spl_object_hash($streamMock), spl_object_hash($requestWithBody->getBody()));
    }

    private function createRequest(
        string $method = '',
        array $adapterOptions = [],
        ?string $content = null,
        ?string $uri = null,
        ?array $files = []
    ): Request {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare($method, [], [], [], [], [], $content, $uri, $files);

        return Request::fromSfWebRequest($symfonyRequestMock, $adapterOptions);
    }
}
