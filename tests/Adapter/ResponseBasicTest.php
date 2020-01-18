<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

class ResponseBasicTest extends TestCase
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
        $this->assertSame('', $response->getProtocolVersion());
        $this->assertSame([], $symfony->getOptions());

        $response = $response->withProtocolVersion('1.1');
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
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
        $this->assertSame('1.0', $response->getProtocolVersion());
        $response = $response->withProtocolVersion('1.1');
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
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
        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('No reason phrase given', $response->getReasonPhrase());
        $this->assertSame(204, $symfony->getStatusCode());
        $this->assertSame('No reason phrase given', $symfony->getStatusText());

        $response = $response->withStatus(200);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame(200, $symfony->getStatusCode());
        $this->assertSame('OK', $symfony->getStatusText());

        $response = $response->withStatus(400, '*** Bad Request ***');
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('*** Bad Request ***', $response->getReasonPhrase());
        $this->assertSame(400, $symfony->getStatusCode());
        $this->assertSame('*** Bad Request ***', $symfony->getStatusText());
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
        $response = $response->withStatus(308);
        $this->assertSame(308, $response->getStatusCode());
        $this->assertSame('Permanent Redirect', $response->getReasonPhrase());
        $this->assertSame(308, $symfony->getStatusCode());
        $this->assertSame('Permanent Redirect', $symfony->getStatusText());
    }

    /**
     * @dataProvider withHeaderProvider
     *
     * @param string $name
     * @param string $value
     * @param array  $expectedHeaders
     * @param array  $expectedHttpHeaders
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
        $this->assertFalse($response->hasHeader($name));
        $this->assertSame([], $response->getHeader($name));
        $this->assertSame([], $symfony->getHttpHeaders());
        $response = $response->withHeader($name, 'FIRST VALUE');
        $response = $response->withHeader($name, $value);
        $this->assertTrue($response->hasHeader($name));
        $this->assertSame([$value], $response->getHeader($name));
        $this->assertSame($value, $response->getHeaderLine($name));
        $this->assertSame($expectedHeaders, $response->getHeaders());
        $this->assertSame($expectedHttpHeaders, $symfony->getHttpHeaders());
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
                    'CONTENT-type' =>
                        ['text/plain'],
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
        $this->assertFalse($response->hasHeader('X-Foo'));
        $this->assertSame([], $response->getHeader('X-Foo'));
        $this->assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'bar');
        $this->assertTrue($response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', 'baz');
        $this->assertTrue($response->hasHeader('X-Foo'));
        $this->assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        $this->assertSame('bar,baz', $response->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        $this->assertSame(['X-Foo' => 'bar,baz'], $symfony->getHttpHeaders());
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
        $this->assertFalse($response->hasHeader('X-Foo'));
        $this->assertSame([], $response->getHeader('X-Foo'));
        $this->assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'foo');
        $this->assertTrue($response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', ['bar', 'baz']);
        $this->assertTrue($response->hasHeader('X-Foo'));
        $this->assertSame(['foo', 'bar', 'baz'], $response->getHeader('X-Foo'));
        $this->assertSame('foo,bar,baz', $response->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $response->getHeaders());
        $this->assertSame(['X-Foo' => 'foo,bar,baz'], $symfony->getHttpHeaders());
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

        $this->assertTrue($response->hasHeader('X-FOO'));
        $this->assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        $this->assertSame('bar, baz', $response->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        $this->assertSame(['X-Foo' => 'bar, baz'], $symfony->getHttpHeaders());
        $response = $response->withoutHeader('x-FoO');
        $this->assertFalse($response->hasHeader('X-Foo'));
        $this->assertSame([], $response->getHeader('X-Foo'));
        $this->assertSame([], $symfony->getHttpHeaders());
    }

    /**
     * @dataProvider withStatusNoContentProvider
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithStatusNoContent(array $adapterOptions, int $initialCode, bool $initialHeaderOnly, int $setCode, bool $expectedHeadersOnly): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse($initialCode, null, [], [], $initialHeaderOnly, [], $adapterOptions);

        $this->assertSame($initialCode, $symfony->getStatusCode());
        $this->assertSame($initialCode, $response->getStatusCode());
        $this->assertSame($initialHeaderOnly, $symfony->isHeaderOnly());

        $newResponse = $response->withStatus($setCode);

        $this->assertSame($setCode, $symfony->getStatusCode());
        $this->assertSame($setCode, $newResponse->getStatusCode());
        $this->assertSame($expectedHeadersOnly, $symfony->isHeaderOnly());
    }

    public function withStatusNoContentProvider(): array
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

    /**
     * @param int         $code
     * @param string|null $reasonPhrase
     * @param string[]    $headers
     * @param array       $cookies
     * @param bool        $headerOnly
     * @param array       $sfOptions
     * @param array       $adapterOptions
     *
     * @return array
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

        return [Response::fromSfWebReponse($symfonyResponseMock, $adapterOptions), $symfonyResponseMock];
    }
}
