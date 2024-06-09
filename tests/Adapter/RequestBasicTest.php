<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \brnc\Symfony1\Message\Adapter\Request
 *
 * @uses   \sfWebRequest
 */
final class RequestBasicTest extends TestCase
{
    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testProtocolVersion(): void
    {
        $request = $this->createRequest();
        self::assertSame('', $request->getProtocolVersion());
        self::assertSame([], $request->getServerParams());
        $request = $request->withProtocolVersion('1.1');
        self::assertSame('1.1', $request->getProtocolVersion());
        self::assertSame(['SERVER_PROTOCOL' => 'HTTP/1.1'], $request->getServerParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testPresetProtocolVersion(): void
    {
        $request = $this->createRequest('GET', ['SERVER_PROTOCOL' => 'HTTP/1.1']);
        self::assertSame('1.1', $request->getProtocolVersion());
        self::assertSame(['SERVER_PROTOCOL' => 'HTTP/1.1'], $request->getServerParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testMethod(): void
    {
        $mock    = $this->createSymfonyMock();
        $request = Request::fromSfWebRequest($mock);
        self::assertSame('GET', $request->getMethod());
        $request = $request->withMethod('PuRgE');
        self::assertSame('PuRgE', $request->getMethod());
        self::assertSame('PURGE', $mock->getMethod());
        $request = $request->withMethod('GET');
        self::assertSame('GET', $request->getMethod());
        self::assertSame('GET', $mock->getMethod());
    }

    /**
     * @dataProvider provideHeaderCases
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testHeader(string $name, string $value, array $expectedHeaders, array $expectedServerParams): void
    {
        $request = $this->createRequest();
        self::assertFalse($request->hasHeader($name));
        self::assertSame([], $request->getHeader($name));
        self::assertSame([], $request->getServerParams());
        $request = $request->withHeader($name, 'FIRST VALUE');
        $request = $request->withHeader($name, $value);
        self::assertTrue($request->hasHeader($name));
        self::assertSame([$value], $request->getHeader($name));
        self::assertSame($value, $request->getHeaderLine($name));
        self::assertSame($expectedHeaders, $request->getHeaders());
        self::assertSame($expectedServerParams, $request->getServerParams());
    }

    public static function provideHeaderCases(): iterable
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

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testWithAddedHeader(): void
    {
        $request = $this->createRequest();
        self::assertFalse($request->hasHeader('X-Foo'));
        self::assertSame([], $request->getHeader('X-Foo'));
        self::assertSame([], $request->getServerParams());
        $request = $request->withAddedHeader('X-Foo', 'bar');
        self::assertTrue($request->hasHeader('X-Foo'));
        $request = $request->withAddedHeader('X-Foo', 'baz');
        self::assertTrue($request->hasHeader('X-Foo'));
        self::assertSame(['bar', 'baz'], $request->getHeader('X-Foo'));
        self::assertSame('bar,baz', $request->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['bar', 'baz']], $request->getHeaders());
        self::assertSame(['HTTP_X_FOO' => 'bar,baz'], $request->getServerParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testWithArrayAddedHeader(): void
    {
        $request = $this->createRequest();
        self::assertFalse($request->hasHeader('X-Foo'));
        self::assertSame([], $request->getHeader('X-Foo'));
        self::assertSame([], $request->getServerParams());
        $request = $request->withAddedHeader('X-Foo', 'foo');
        self::assertTrue($request->hasHeader('X-Foo'));
        $request = $request->withAddedHeader('X-Foo', ['bar', 'baz']);
        self::assertTrue($request->hasHeader('X-Foo'));
        self::assertSame(['foo', 'bar', 'baz'], $request->getHeader('X-Foo'));
        self::assertSame('foo,bar,baz', $request->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $request->getHeaders());
        self::assertSame(['HTTP_X_FOO' => 'foo,bar,baz'], $request->getServerParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testWithoutHeader(): void
    {
        $request = $this->createRequest('GET', ['HTTP_X_FOO' => 'bar, baz']);
        self::assertTrue($request->hasHeader('X-Foo'));
        self::assertSame(['bar', 'baz'], $request->getHeader('X-Foo'));
        self::assertSame('bar, baz', $request->getHeaderLine('X-Foo'));
        self::assertSame(['x-foo' => ['bar', 'baz']], $request->getHeaders());
        self::assertSame(['HTTP_X_FOO' => 'bar, baz'], $request->getServerParams());
        $request = $request->withoutHeader('x-FoO');
        self::assertFalse($request->hasHeader('X-Foo'));
        self::assertSame([], $request->getHeader('X-Foo'));
        self::assertSame([], $request->getServerParams());
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
