<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class ServerRequestBodyTest extends TestCase
{
    public function testConstructorGetBody()
    {
        $body = $this->createRequest('POST')->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertSame('', $body->getContents(), 'Expected empty stream.');
        $this->assertSame(true, $body->isReadable(), 'Default getBody() should be readable.');
        $this->assertSame(true, $body->isWritable(), 'Default getBody() is writable as a quirk.');
    }

    public function testStaticConstructionGetBodyHasContent()
    {
        $body = $this->createRequest('POST', [], 'dummy content')->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertSame('dummy content', $body->getContents(), 'Expected stream to have content.');
        $this->assertSame(true, $body->isReadable(), 'Static constructed getBody() should be readable.');
        $this->assertSame(true, $body->isWritable(), 'Static constructed getBody() is writable as a quirk.');
    }

    public function testStaticConstructionUsingPhpInputGetBody()
    {
        $body = $this->createRequest('POST', [Request::OPTION_BODY_USE_STREAM => true])->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertSame('', $body->getContents(), 'Expected empty stream.');
        $this->assertSame(true, $body->isReadable(), 'Static constructed getBody() should be readable.');
        $this->assertSame(true, $body->isWritable(), 'Static constructed  getBody() is writable as a quirk.');
    }

    /**
     * @expectedException \LogicException
     */
    public function testItFailsWithBody()
    {
        $request    = $this->createRequest();
        $newRequest = $request->withBody($this->createMock(StreamInterface::class));
    }

    /**
     * @param string|null $method
     * @param array       $adapterOptions
     * @param string|null $content
     * @param string|null $uri
     *
     * @return Request
     */
    private function createRequest(
        ?string $method = null,
        array $adapterOptions = [],
        ?string $content = null,
        ?string $uri = null
    ): Request {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare($method, [], [], [], [], [], $content, $uri);

        return Request::fromSfWebRequest($symfonyRequestMock, $adapterOptions);
    }
}