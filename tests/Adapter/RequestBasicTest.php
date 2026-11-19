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
     * @dataProvider provideImmutableViolationOptionIsReadByValueCases
     *
     * @param array<string, mixed> $options
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testImmutableViolationOptionIsReadByValue(array $options, bool $expectMutable): void
    {
        /** @var array<string, bool> $options callers without static analysis can pass other values */
        $request = Request::fromSfWebRequest($this->createSymfonyMock(), $options);
        self::assertSame($expectMutable, $request === $request->withHeader('X-Foo', 'bar'));
    }

    /** @return iterable<string, array{array<string, mixed>, bool}> */
    public static function provideImmutableViolationOptionIsReadByValueCases(): iterable
    {
        return [
            'not given: on'          => [[], true],
            'null: on, as not given' => [[Request::OPTION_IMMUTABLE_VIOLATION => null], true],
            'true: on'               => [[Request::OPTION_IMMUTABLE_VIOLATION => true], true],
            'false: off'             => [[Request::OPTION_IMMUTABLE_VIOLATION => false], false],
            '0: off'                 => [[Request::OPTION_IMMUTABLE_VIOLATION => 0], false],
            'empty string: off'      => [[Request::OPTION_IMMUTABLE_VIOLATION => ''], false],
            'string 0: off'          => [[Request::OPTION_IMMUTABLE_VIOLATION => '0'], false],
        ];
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
        self::assertSame('bar, baz', $request->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['bar', 'baz']], $request->getHeaders());
        self::assertSame(['HTTP_X_FOO' => 'bar, baz'], $request->getServerParams());
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
        self::assertSame('foo, bar, baz', $request->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $request->getHeaders());
        self::assertSame(['HTTP_X_FOO' => 'foo, bar, baz'], $request->getServerParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testWithoutHeader(): void
    {
        $request = $this->createRequest('GET', ['HTTP_X_FOO' => 'bar, baz']);
        self::assertTrue($request->hasHeader('X-Foo'));
        self::assertSame(['bar, baz'], $request->getHeader('X-Foo'));
        self::assertSame('bar, baz', $request->getHeaderLine('X-Foo'));
        self::assertSame(['x-foo' => ['bar, baz']], $request->getHeaders());
        self::assertSame(['HTTP_X_FOO' => 'bar, baz'], $request->getServerParams());
        $request = $request->withoutHeader('x-FoO');
        self::assertFalse($request->hasHeader('X-Foo'));
        self::assertSame([], $request->getHeader('X-Foo'));
        self::assertSame([], $request->getServerParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testHeaderValuesWithCommasRoundTrip(): void
    {
        $request = $this->createRequest()->withHeader('X-Foo', ['a, b', 'c']);
        self::assertSame(['a, b', 'c'], $request->getHeader('X-Foo'));
        self::assertSame('a, b, c', $request->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['a, b', 'c']], $request->getHeaders());
        self::assertSame(['HTTP_X_FOO' => 'a, b, c'], $request->getServerParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testHeaderValuesAreReadFromSymfonyAfterAnotherAdapterChangedThem(): void
    {
        $symfony = $this->createSymfonyMock();
        $first   = Request::fromSfWebRequest($symfony)->withHeader('X-Foo', ['bar', 'baz']);
        Request::fromSfWebRequest($symfony)->withHeader('X-Foo', 'qux, quux');
        self::assertSame(['qux, quux'], $first->getHeader('X-Foo'));
        self::assertSame(['X-Foo' => ['qux, quux']], $first->getHeaders());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testWithoutHeaderForgetsHeaderValues(): void
    {
        $symfony = $this->createSymfonyMock();
        $first   = Request::fromSfWebRequest($symfony)->withHeader('X-Foo', ['a', 'b'])->withoutHeader('X-Foo');
        Request::fromSfWebRequest($symfony)->withHeader('X-Foo', 'a, b');
        self::assertSame(['a, b'], $first->getHeader('X-Foo'));
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testHeaderValuesWithBackslashesRoundTrip(): void
    {
        $request = $this->createRequest()->withHeader('X-Path', ['C:\\dir', 'b']);
        self::assertSame(['C:\\dir', 'b'], $request->getHeader('X-Path'));
        self::assertSame(['X-Path' => ['C:\\dir', 'b']], $request->getHeaders());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testHeaderLineHeldBySymfonyIsReadWithoutBackslashes(): void
    {
        $request = $this->createRequest('GET', ['HTTP_X_PATH' => 'C:\\dir, b']);
        self::assertSame(['C:dir, b'], $request->getHeader('X-Path'));
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testHeaderValuesAreTrimmedOfSpacesAndTabs(): void
    {
        $request = $this->createRequest('GET', ['HTTP_X_FOO' => " bar\t"]);
        self::assertSame(['bar'], $request->getHeader('X-Foo'));
        self::assertSame(['x-foo' => ['bar']], $request->getHeaders());
        self::assertSame(" bar\t", $request->getHeaderLine('X-Foo'));

        $request = $request->withHeader('X-Foo', [' a ', "b\t"]);
        self::assertSame(['a', 'b'], $request->getHeader('X-Foo'));
        self::assertSame(['X-Foo' => ['a', 'b']], $request->getHeaders());
        self::assertSame(" a , b\t", $request->getHeaderLine('X-Foo'));
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
