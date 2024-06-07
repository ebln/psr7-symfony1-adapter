<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ResponseBasicTest extends TestCase
{
    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testProtocolVersion(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        self::assertSame('', $response->getProtocolVersion());
        self::assertSame([], $symfony->getOptions());

        $response = $response->withProtocolVersion('1.1');
        self::assertSame('1.1', $response->getProtocolVersion());
        self::assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testPresetProtocolVersion(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(200, null, [], [], false, ['http_protocol' => 'HTTP/1.0']);
        self::assertSame('1.0', $response->getProtocolVersion());
        $response = $response->withProtocolVersion('1.1');
        self::assertSame('1.1', $response->getProtocolVersion());
        self::assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testStatus(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(204);
        self::assertSame(204, $response->getStatusCode());
        self::assertSame('No reason phrase given', $response->getReasonPhrase());
        self::assertSame(204, $symfony->getStatusCode());
        self::assertSame('No reason phrase given', $symfony->getStatusText());

        $response = $response->withStatus(200);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('OK', $response->getReasonPhrase());
        self::assertSame(200, $symfony->getStatusCode());
        self::assertSame('OK', $symfony->getStatusText());

        $response = $response->withStatus(400, '*** Bad Request ***');
        self::assertSame(400, $response->getStatusCode());
        self::assertSame('*** Bad Request ***', $response->getReasonPhrase());
        self::assertSame(400, $symfony->getStatusCode());
        self::assertSame('*** Bad Request ***', $symfony->getStatusText());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testDefaultReasonPhrase(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(204);
        $response             = $response->withStatus(308);
        self::assertSame(308, $response->getStatusCode());
        self::assertSame('Permanent Redirect', $response->getReasonPhrase());
        self::assertSame(308, $symfony->getStatusCode());
        self::assertSame('Permanent Redirect', $symfony->getStatusText());
    }

    /**
     * @dataProvider provideHeaderCases
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testHeader(string $name, string $value, array $expectedHeaders, array $expectedHttpHeaders): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        self::assertFalse($response->hasHeader($name));
        self::assertSame([], $response->getHeader($name));
        self::assertSame([], $symfony->getHttpHeaders());
        $response = $response->withHeader($name, 'FIRST VALUE');
        $response = $response->withHeader($name, $value);
        self::assertTrue($response->hasHeader($name));
        self::assertSame([$value], $response->getHeader($name));
        self::assertSame($value, $response->getHeaderLine($name));
        self::assertSame($expectedHeaders, $response->getHeaders());
        self::assertSame($expectedHttpHeaders, $symfony->getHttpHeaders());
    }

    public static function provideHeaderCases(): iterable
    {
        return [
            [
                'X-Foo',
                'bar',
                ['X-Foo' => ['bar']],
                ['X-Foo' => 'bar'],
            ],
            [
                'CONTENT-type',
                'text/plain',
                [
                    'CONTENT-type' => ['text/plain'],
                ],
                ['Content-Type' => 'text/plain'],
            ],
        ];
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testwithAddedHeader(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        self::assertFalse($response->hasHeader('X-Foo'));
        self::assertSame([], $response->getHeader('X-Foo'));
        self::assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'bar');
        self::assertTrue($response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', 'baz');
        self::assertTrue($response->hasHeader('X-Foo'));
        self::assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        self::assertSame('bar,baz', $response->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        self::assertSame(['X-Foo' => 'bar,baz'], $symfony->getHttpHeaders());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testwithArrayAddedHeader(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        self::assertFalse($response->hasHeader('X-Foo'));
        self::assertSame([], $response->getHeader('X-Foo'));
        self::assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'foo');
        self::assertTrue($response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', ['bar', 'baz']);
        self::assertTrue($response->hasHeader('X-Foo'));
        self::assertSame(['foo', 'bar', 'baz'], $response->getHeader('X-Foo'));
        self::assertSame('foo,bar,baz', $response->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $response->getHeaders());
        self::assertSame(['X-Foo' => 'foo,bar,baz'], $symfony->getHttpHeaders());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testwithoutHeader(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(204, 'No Content', ['X-Foo' => 'bar, baz']);

        self::assertTrue($response->hasHeader('X-FOO'));
        self::assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        self::assertSame('bar, baz', $response->getHeaderLine('X-Foo'));
        self::assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        self::assertSame(['X-Foo' => 'bar, baz'], $symfony->getHttpHeaders());
        $response = $response->withoutHeader('x-FoO');
        self::assertFalse($response->hasHeader('X-Foo'));
        self::assertSame([], $response->getHeader('X-Foo'));
        self::assertSame([], $symfony->getHttpHeaders());
    }

    /**
     * @dataProvider provideWithStatusNoContentCases
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithStatusNoContent(array $adapterOptions, int $initialCode, bool $initialHeaderOnly, int $setCode, bool $expectedHeadersOnly): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse($initialCode, null, [], [], $initialHeaderOnly, [], $adapterOptions);

        self::assertSame($initialCode, $symfony->getStatusCode());
        self::assertSame($initialCode, $response->getStatusCode());
        self::assertSame($initialHeaderOnly, $symfony->isHeaderOnly());

        $newResponse = $response->withStatus($setCode);

        self::assertSame($setCode, $symfony->getStatusCode());
        self::assertSame($setCode, $newResponse->getStatusCode());
        self::assertSame($expectedHeadersOnly, $symfony->isHeaderOnly());
    }

    public static function provideWithStatusNoContentCases(): iterable
    {
        return [
            '200 → 204 - default: set headersOnly true'                            => [
                'factory options'      => [],
                'initial status'       => 200,
                'initial headers only' => false,
                'set to status'        => 204,
                'expect headers only'  => true,
            ],
            '204 with setHeadersOnly(true) → 200 - default: set headersOnly false' => [
                'factory options'      => [],
                'initial status'       => 204,
                'initial headers only' => true,
                'set to status'        => 200,
                'expect headers only'  => false,
            ],
            '200 → 201 - default : no change'                                      => [
                'factory options'      => [],
                'initial status'       => 200,
                'initial headers only' => false,
                'set to status'        => 201,
                'expect headers only'  => false,
            ],
            '200 → 204 - no automagic'                                             => [
                'factory options'      => [Response::OPTION_SEND_BODY_ON_204 => true],
                'initial status'       => 200,
                'initial headers only' => false,
                'set to status'        => 204,
                'expect headers only'  => false,
            ],
            '200 with setHeadersOnly(true) → 204: no change'                       => [
                'factory options'      => [],
                'initial status'       => 200,
                'initial headers only' => true,
                'set to status'        => 204,
                'expect headers only'  => true,
            ],
            '204 with setHeadersOnly(false) → 200: no change'                      => [
                'factory options'      => [],
                'initial status'       => 204,
                'initial headers only' => false,
                'set to status'        => 200,
                'expect headers only'  => false,
            ],
            '204 with setHeadersOnly(true) → 204: no change'                       => [
                'factory options'      => [],
                'initial status'       => 204,
                'initial headers only' => true,
                'set to status'        => 204,
                'expect headers only'  => true,
            ],
            '204 with setHeadersOnly(false) → 204: no change'                      => [
                'factory options'      => [],
                'initial status'       => 204,
                'initial headers only' => false,
                'set to status'        => 200,
                'expect headers only'  => false,
            ],
        ];
    }

    /** @param string[] $headers */
    private function createResponse(
        int $code = 200,
        ?string $reasonPhrase = null,
        array $headers = [],
        array $cookies = [],
        bool $headerOnly = false,
        array $sfOptions = [],
        array $adapterOptions = []
    ): array {
        $symfonyResponseMock = new \sfWebResponse(null, $sfOptions);
        $symfonyResponseMock->prepare($code, $reasonPhrase, $headers, $cookies, $headerOnly);

        return [Response::fromSfWebResponse($symfonyResponseMock, $adapterOptions), $symfonyResponseMock];
    }
}
