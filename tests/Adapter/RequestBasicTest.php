<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RequestBasicTest extends TestCase
{
    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testProtocolVersion(): void
    {
        $request = $this->createRequest();
        static::assertSame('', $request->getProtocolVersion());
        static::assertSame([], $request->getServerParams());
        $request = $request->withProtocolVersion('1.1');
        static::assertSame('1.1', $request->getProtocolVersion());
        static::assertSame(['SERVER_PROTOCOL' => 'HTTP/1.1'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testPresetProtocolVersion(): void
    {
        $request = $this->createRequest('GET', ['SERVER_PROTOCOL' => 'HTTP/1.1']);
        static::assertSame('1.1', $request->getProtocolVersion());
        static::assertSame(['SERVER_PROTOCOL' => 'HTTP/1.1'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testMethod(): void
    {
        $mock    = $this->createSymfonyMock();
        $request = Request::fromSfWebRequest($mock);
        static::assertSame('GET', $request->getMethod());
        $request = $request->withMethod('PuRgE');
        static::assertSame('PuRgE', $request->getMethod());
        static::assertSame('PURGE', $mock->getMethod());
        $request = $request->withMethod('GET');
        static::assertSame('GET', $request->getMethod());
        static::assertSame('GET', $mock->getMethod());
    }

    /**
     * @dataProvider withHeaderProvider
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testHeader(string $name, string $value, array $expectedHeaders, array $expectedServerParams): void
    {
        $request = $this->createRequest();
        static::assertFalse($request->hasHeader($name));
        static::assertSame([], $request->getHeader($name));
        static::assertSame([], $request->getServerParams());
        $request = $request->withHeader($name, 'FIRST VALUE');
        $request = $request->withHeader($name, $value);
        static::assertTrue($request->hasHeader($name));
        static::assertSame([$value], $request->getHeader($name));
        static::assertSame($value, $request->getHeaderLine($name));
        static::assertSame($expectedHeaders, $request->getHeaders());
        static::assertSame($expectedServerParams, $request->getServerParams());
    }

    public function withHeaderProvider(): array
    {
        return [
            [
                'X-Foo',
                'bar',
                ['X-Foo'      => ['bar']],
                ['HTTP_X_FOO' => 'bar'],
            ],
            [
                'Content-Length',
                '42',
                ['Content-Length' => ['42']],
                ['CONTENT_LENGTH' => '42'],
            ],
            [
                'content-md5',
                'deadbeef',
                [
                    'content-md5' => ['deadbeef'],
                ],
                ['CONTENT_MD5' => 'deadbeef'],
            ],
            [
                'CONTENT-type',
                'text/plain',
                [
                    'CONTENT-type' => ['text/plain'],
                ],
                ['CONTENT_TYPE' => 'text/plain'],
            ],
        ];
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithAddedHeader(): void
    {
        $request = $this->createRequest();
        static::assertFalse($request->hasHeader('X-Foo'));
        static::assertSame([], $request->getHeader('X-Foo'));
        static::assertSame([], $request->getServerParams());
        $request = $request->withAddedHeader('X-Foo', 'bar');
        static::assertTrue($request->hasHeader('X-Foo'));
        $request = $request->withAddedHeader('X-Foo', 'baz');
        static::assertTrue($request->hasHeader('X-Foo'));
        static::assertSame(['bar', 'baz'], $request->getHeader('X-Foo'));
        static::assertSame('bar,baz', $request->getHeaderLine('X-Foo'));
        static::assertSame(['X-Foo' => ['bar', 'baz']], $request->getHeaders());
        static::assertSame(['HTTP_X_FOO' => 'bar,baz'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithArrayAddedHeader(): void
    {
        $request = $this->createRequest();
        static::assertFalse($request->hasHeader('X-Foo'));
        static::assertSame([], $request->getHeader('X-Foo'));
        static::assertSame([], $request->getServerParams());
        $request = $request->withAddedHeader('X-Foo', 'foo');
        static::assertTrue($request->hasHeader('X-Foo'));
        $request = $request->withAddedHeader('X-Foo', ['bar', 'baz']);
        static::assertTrue($request->hasHeader('X-Foo'));
        static::assertSame(['foo', 'bar', 'baz'], $request->getHeader('X-Foo'));
        static::assertSame('foo,bar,baz', $request->getHeaderLine('X-Foo'));
        static::assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $request->getHeaders());
        static::assertSame(['HTTP_X_FOO' => 'foo,bar,baz'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithoutHeader(): void
    {
        $request = $this->createRequest('GET', ['HTTP_X_FOO' => 'bar, baz']);
        static::assertTrue($request->hasHeader('X-Foo'));
        static::assertSame(['bar', 'baz'], $request->getHeader('X-Foo'));
        static::assertSame('bar, baz', $request->getHeaderLine('X-Foo'));
        static::assertSame(['x-foo' => ['bar', 'baz']], $request->getHeaders());
        static::assertSame(['HTTP_X_FOO' => 'bar, baz'], $request->getServerParams());
        $request = $request->withoutHeader('x-FoO');
        static::assertFalse($request->hasHeader('X-Foo'));
        static::assertSame([], $request->getHeader('X-Foo'));
        static::assertSame([], $request->getServerParams());
    }

    private function createRequest(
        string $method = 'GET',
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $requestParameters = [],
        array $options = []
    ): Request {
        return Request::fromSfWebRequest($this->createSymfonyMock($method, $server, $get, $post, $cookie, $requestParameters, $options));
    }

    private function createSymfonyMock(
        string $method = 'GET',
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $requestParameters = [],
        array $options = []
    ): \sfWebRequest {
        $symfonyRequestMock = new \sfWebRequest(null, [], [], $options);
        $symfonyRequestMock->prepare($method, $server, $get, $post, $cookie, $requestParameters);

        return $symfonyRequestMock;
    }
}
