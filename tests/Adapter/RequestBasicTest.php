<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

class RequestBasicTest extends TestCase
{
    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testProtocolVersion(): void
    {
        $request = $this->createRequest();
        $this->assertSame('', $request->getProtocolVersion());
        $this->assertSame([], $request->getServerParams());
        $request = $request->withProtocolVersion('1.1');
        $this->assertSame('1.1', $request->getProtocolVersion());
        $this->assertSame(['SERVER_PROTOCOL' => 'HTTP/1.1'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testPresetProtocolVersion(): void
    {
        $request = $this->createRequest('GET', ['SERVER_PROTOCOL' => 'HTTP/1.1']);
        $this->assertSame('1.1', $request->getProtocolVersion());
        $this->assertSame(['SERVER_PROTOCOL' => 'HTTP/1.1'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testMethod(): void
    {
        $mock    = $this->createSymfonyMock();
        $request = Request::fromSfWebRequest($mock);
        $this->assertSame('GET', $request->getMethod());
        $request = $request->withMethod('PuRgE');
        $this->assertSame('PuRgE', $request->getMethod());
        $this->assertSame('PURGE', $mock->getMethod());
        $request = $request->withMethod('GET');
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('GET', $mock->getMethod());
    }

    /**
     * @dataProvider withHeaderProvider
     *
     * @param string $name
     * @param string $value
     * @param array  $expectedHeaders
     * @param array  $expectedServerParams
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testHeader(string $name, string $value, array $expectedHeaders, array $expectedServerParams): void
    {
        $request = $this->createRequest();
        $this->assertFalse($request->hasHeader($name));
        $this->assertSame([], $request->getHeader($name));
        $this->assertSame([], $request->getServerParams());
        $request = $request->withHeader($name, 'FIRST VALUE');
        $request = $request->withHeader($name, $value);
        $this->assertSame(true, $request->hasHeader($name));
        $this->assertSame([$value], $request->getHeader($name));
        $this->assertSame($value, $request->getHeaderLine($name));
        $this->assertSame($expectedHeaders, $request->getHeaders());
        $this->assertSame($expectedServerParams, $request->getServerParams());
    }

    public function withHeaderProvider(): array
    {
        return [
            [
                'X-Foo',
                'bar',
                ['X-Foo' => ['bar']],
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
                    'content-md5' =>
                        ['deadbeef'],
                ],
                ['CONTENT_MD5' => 'deadbeef'],
            ],
            [
                'CONTENT-type',
                'text/plain',
                [
                    'CONTENT-type' =>
                        ['text/plain'],
                ],
                ['CONTENT_TYPE' => 'text/plain'],
            ],
        ];
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testwithAddedHeader(): void
    {
        $request = $this->createRequest();
        $this->assertSame(false, $request->hasHeader('X-Foo'));
        $this->assertSame([], $request->getHeader('X-Foo'));
        $this->assertSame([], $request->getServerParams());
        $request = $request->withAddedHeader('X-Foo', 'bar');
        $this->assertSame(true, $request->hasHeader('X-Foo'));
        $request = $request->withAddedHeader('X-Foo', 'baz');
        $this->assertSame(true, $request->hasHeader('X-Foo'));
        $this->assertSame(['bar', 'baz'], $request->getHeader('X-Foo'));
        $this->assertSame('bar,baz', $request->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['bar', 'baz']], $request->getHeaders());
        $this->assertSame(['HTTP_X_FOO' => 'bar,baz'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testwithArrayAddedHeader(): void
    {
        $request = $this->createRequest();
        $this->assertSame(false, $request->hasHeader('X-Foo'));
        $this->assertSame([], $request->getHeader('X-Foo'));
        $this->assertSame([], $request->getServerParams());
        $request = $request->withAddedHeader('X-Foo', 'foo');
        $this->assertSame(true, $request->hasHeader('X-Foo'));
        $request = $request->withAddedHeader('X-Foo', ['bar', 'baz']);
        $this->assertSame(true, $request->hasHeader('X-Foo'));
        $this->assertSame(['foo', 'bar', 'baz'], $request->getHeader('X-Foo'));
        $this->assertSame('foo,bar,baz', $request->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $request->getHeaders());
        $this->assertSame(['HTTP_X_FOO' => 'foo,bar,baz'], $request->getServerParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testwithoutHeader(): void
    {
        $request = $this->createRequest('GET', ['HTTP_X_FOO' => 'bar, baz']);
        $this->assertSame(true, $request->hasHeader('X-Foo'));
        $this->assertSame(['bar', 'baz'], $request->getHeader('X-Foo'));
        $this->assertSame('bar, baz', $request->getHeaderLine('X-Foo'));
        $this->assertSame(['x-foo' => ['bar', 'baz']], $request->getHeaders());
        $this->assertSame(['HTTP_X_FOO' => 'bar, baz'], $request->getServerParams());
        $request = $request->withoutHeader('x-FoO');
        $this->assertSame(false, $request->hasHeader('X-Foo'));
        $this->assertSame([], $request->getHeader('X-Foo'));
        $this->assertSame([], $request->getServerParams());
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
