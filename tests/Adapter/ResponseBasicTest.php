<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

class ResponseBasicTest extends TestCase
{
    public function testProtocolVersion()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse();
        $this->assertSame('', $response->getProtocolVersion());
        $this->assertSame([], $symfony->getOptions());

        $response = $response->withProtocolVersion('1.1');
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    public function testPresetProtocolVersion()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse(200, null, [], [], ['http_protocol' => 'HTTP/1.0']);
        $this->assertSame('1.0', $response->getProtocolVersion());
        $response = $response->withProtocolVersion('1.1');
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    public function testStatus()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse(204);
        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('No reason phrase given', $response->getReasonPhrase());
        $this->assertSame(204, $symfony->getStatusCode());
        $this->assertSame('No reason phrase given', $symfony->getStatusText());

        $response = $response->withStatus('200');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame(200, $symfony->getStatusCode());
        $this->assertSame('OK', $symfony->getStatusText());

        $response = $response->withStatus('400', '*** Bad Request ***');
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('*** Bad Request ***', $response->getReasonPhrase());
        $this->assertSame(400, $symfony->getStatusCode());
        $this->assertSame('*** Bad Request ***', $symfony->getStatusText());
    }

    /**
     * @dataProvider withHeaderProvider
     *
     * @param string $name
     * @param string $value
     * @param array  $expectedHeaders
     * @param        $expectedInteral
     */
    public function testHeader($name, $value, $expectedHeaders, $expectedInteral)
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse();
        $this->assertFalse($response->hasHeader($name));
        $this->assertSame([], $response->getHeader($name));
        $this->assertSame([], $symfony->getHttpHeaders());
        $response = $response->withHeader($name, 'FIRST VALUE');
        $response = $response->withHeader($name, $value);
        $this->assertSame(true, $response->hasHeader($name));
        $this->assertSame([$value], $response->getHeader($name));
        $this->assertSame($value, $response->getHeaderLine($name));
        $this->assertSame($expectedHeaders, $response->getHeaders());
        $this->assertSame($expectedInteral, $symfony->getHttpHeaders());
    }

    /**
     * @return array
     */
    public function withHeaderProvider()
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

    public function testwithAddedHeader()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse();
        $this->assertSame(false, $response->hasHeader('X-Foo'));
        $this->assertSame([], $response->getHeader('X-Foo'));
        $this->assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'bar');
        $this->assertSame(true, $response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', 'baz');
        $this->assertSame(true, $response->hasHeader('X-Foo'));
        $this->assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        $this->assertSame('bar,baz', $response->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        $this->assertSame(['X-Foo' => 'bar,baz'], $symfony->getHttpHeaders());
    }

    public function testwithArrayAddedHeader()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse();
        $this->assertSame(false, $response->hasHeader('X-Foo'));
        $this->assertSame([], $response->getHeader('X-Foo'));
        $this->assertSame([], $symfony->getHttpHeaders());
        $response = $response->withAddedHeader('X-Foo', 'foo');
        $this->assertSame(true, $response->hasHeader('X-Foo'));
        $response = $response->withAddedHeader('X-Foo', ['bar', 'baz']);
        $this->assertSame(true, $response->hasHeader('X-Foo'));
        $this->assertSame(['foo', 'bar', 'baz'], $response->getHeader('X-Foo'));
        $this->assertSame('foo,bar,baz', $response->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['foo', 'bar', 'baz']], $response->getHeaders());
        $this->assertSame(['X-Foo' => 'foo,bar,baz'], $symfony->getHttpHeaders());
    }

    public function testwithoutHeader()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse(204, 'No Content', ['X-Foo' => 'bar, baz']);

        $this->assertSame(true, $response->hasHeader('X-FOO'));
        $this->assertSame(['bar', 'baz'], $response->getHeader('X-Foo'));
        $this->assertSame('bar, baz', $response->getHeaderLine('X-Foo'));
        $this->assertSame(['X-Foo' => ['bar', 'baz']], $response->getHeaders());
        $this->assertSame(['X-Foo' => 'bar, baz'], $symfony->getHttpHeaders());
        $response = $response->withoutHeader('x-FoO');
        $this->assertSame(false, $response->hasHeader('X-Foo'));
        $this->assertSame([], $response->getHeader('X-Foo'));
        $this->assertSame([], $symfony->getHttpHeaders());
    }

    /**
     * @param int         $code
     * @param string|null $reasonPhrase
     * @param string[]    $headers
     * @param array       $cookies
     * @param array       $options
     *
     * @return array
     */
    private function createResponse(
        $code = 200,
        $reasonPhrase = null,
        $headers = [],
        $cookies = [],
        array $options = []
    ) {
        $symfonyResponseMock = new \sfWebResponse(null, $options);
        $symfonyResponseMock->prepare($code, $reasonPhrase, $headers, $cookies);

        return [new Response($symfonyResponseMock), $symfonyResponseMock];
    }
}
