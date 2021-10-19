<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ResponseBasicTest extends TestCase
{
    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testProtocolVersion(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        static::assertSame('', $response->getProtocolVersion());
        static::assertSame([], $symfony->getOptions());

        $response = $response->withProtocolVersion('1.1');
        static::assertSame('1.1', $response->getProtocolVersion());
        static::assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testPresetProtocolVersion(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(200, null, [], [], false, ['http_protocol' => 'HTTP/1.0']);
        static::assertSame('1.0', $response->getProtocolVersion());
        $response = $response->withProtocolVersion('1.1');
        static::assertSame('1.1', $response->getProtocolVersion());
        static::assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testStatus(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(204);
        static::assertSame(204, $response->getStatusCode());
        static::assertSame('No reason phrase given', $response->getReasonPhrase());
        static::assertSame(204, $symfony->getStatusCode());
        static::assertSame('No reason phrase given', $symfony->getStatusText());

        $response = $response->withStatus(200);
        static::assertSame(200, $response->getStatusCode());
        static::assertSame('OK', $response->getReasonPhrase());
        static::assertSame(200, $symfony->getStatusCode());
        static::assertSame('OK', $symfony->getStatusText());

        $response = $response->withStatus(400, '*** Bad Request ***');
        static::assertSame(400, $response->getStatusCode());
        static::assertSame('*** Bad Request ***', $response->getReasonPhrase());
        static::assertSame(400, $symfony->getStatusCode());
        static::assertSame('*** Bad Request ***', $symfony->getStatusText());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testDefaultReasonPhrase(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(204);
        $response             = $response->withStatus(308);
        static::assertSame(308, $response->getStatusCode());
        static::assertSame('Permanent Redirect', $response->getReasonPhrase());
        static::assertSame(308, $symfony->getStatusCode());
        static::assertSame('Permanent Redirect', $symfony->getStatusText());
    }

    /**
     * @dataProvider withHeaderProvider
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
        static::assertFalse($response->hasHeader($name));
        static::assertSame([], $response->getHeader($name));
        static::assertSame([], $symfony->getHttpHeaders());
        $response = $response->withHeader($name, 'FIRST VALUE');
        $response = $response->withHeader($name, $value);
        static::assertTrue($response->hasHeader($name));
        static::assertSame([$value], $response->getHeader($name));
        static::assertSame($value, $response->getHeaderLine($name));
        static::assertSame($expectedHeaders, $response->getHeaders());
        static::assertSame($expectedHttpHeaders, $symfony->getHttpHeaders());
    }

    public function withHeaderProvider(): array
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

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testwithAddedHeader(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        static::assertFalse($response->hasHeader('X-Foo'));
        static::assertSame([], $response->getHeader('X-Foo'));
        static::assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'bar');
        static::assertTrue($response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', 'baz');
        static::assertTrue($response->hasHeader('X-Foo'));
        static::assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        static::assertSame('bar,baz', $response->getHeaderLine('X-Foo'));
        static::assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        static::assertSame(['X-Foo' => 'bar,baz'], $symfony->getHttpHeaders());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testwithArrayAddedHeader(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        static::assertFalse($response->hasHeader('X-Foo'));
        static::assertSame([], $response->getHeader('X-Foo'));
        static::assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'foo');
        static::assertTrue($response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', ['bar', 'baz']);
        static::assertTrue($response->hasHeader('X-Foo'));
        static::assertSame(['foo', 'bar', 'baz'], $response->getHeader('X-Foo'));
        static::assertSame('foo,bar,baz', $response->getHeaderLine('X-Foo'));
        static::assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $response->getHeaders());
        static::assertSame(['X-Foo' => 'foo,bar,baz'], $symfony->getHttpHeaders());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testwithoutHeader(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse(204, 'No Content', ['X-Foo' => 'bar, baz']);

        static::assertTrue($response->hasHeader('X-FOO'));
        static::assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        static::assertSame('bar, baz', $response->getHeaderLine('X-Foo'));
        static::assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        static::assertSame(['X-Foo' => 'bar, baz'], $symfony->getHttpHeaders());
        $response = $response->withoutHeader('x-FoO');
        static::assertFalse($response->hasHeader('X-Foo'));
        static::assertSame([], $response->getHeader('X-Foo'));
        static::assertSame([], $symfony->getHttpHeaders());
    }

    /**
     * @dataProvider withStatusNoContentProvider
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

        static::assertSame($initialCode, $symfony->getStatusCode());
        static::assertSame($initialCode, $response->getStatusCode());
        static::assertSame($initialHeaderOnly, $symfony->isHeaderOnly());

        $newResponse = $response->withStatus($setCode);

        static::assertSame($setCode, $symfony->getStatusCode());
        static::assertSame($setCode, $newResponse->getStatusCode());
        static::assertSame($expectedHeadersOnly, $symfony->isHeaderOnly());
    }

    public function withStatusNoContentProvider(): array
    {
        return [
            '200 → 204 - default: set headersOnly true' => [
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
            '200 → 201 - default : no change' => [
                'factory options'      => [],
                'initial status'       => 200,
                'initial headers only' => false,
                'set to status'        => 201,
                'expect headers only'  => false,
            ],
            '200 → 204 - no automagic' => [
                'factory options'      => [Response::OPTION_SEND_BODY_ON_204 => true],
                'initial status'       => 200,
                'initial headers only' => false,
                'set to status'        => 204,
                'expect headers only'  => false,
            ],
            '200 with setHeadersOnly(true) → 204: no change' => [
                'factory options'      => [],
                'initial status'       => 200,
                'initial headers only' => true,
                'set to status'        => 204,
                'expect headers only'  => true,
            ],
            '204 with setHeadersOnly(false) → 200: no change' => [
                'factory options'      => [],
                'initial status'       => 204,
                'initial headers only' => false,
                'set to status'        => 200,
                'expect headers only'  => false,
            ],
            '204 with setHeadersOnly(true) → 204: no change' => [
                'factory options'      => [],
                'initial status'       => 204,
                'initial headers only' => true,
                'set to status'        => 204,
                'expect headers only'  => true,
            ],
            '204 with setHeadersOnly(false) → 204: no change' => [
                'factory options'      => [],
                'initial status'       => 204,
                'initial headers only' => false,
                'set to status'        => 200,
                'expect headers only'  => false,
            ],
        ];
    }

    /**
     * @param string[] $headers
     */
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
