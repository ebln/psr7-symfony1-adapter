<?php

namespace brnc\Tests\Symfony1\Message;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

/**
 * tests only edge-cases for setHttpHeader of mocked sfWebResponse
 * which has not been inplictily covered by Adapter tests
 */
class ResponseMockTest extends TestCase
{
    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetHttpHeaderAppend()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse();
        $this->assertFalse($response->hasHeader('X-Append-Test'));
        $this->assertSame([], $response->getHeader('X-Append-Test'));
        $this->assertSame([], $symfony->getHttpHeaders());

        $response = $response->withHeader('X-Append-Test', 'foo/bar');

        $this->assertSame(true, $response->hasHeader('X-Append-Test'));
        $this->assertSame(['foo/bar'], $response->getHeader('X-Append-Test'));
        $this->assertSame('foo/bar', $response->getHeaderLine('X-Append-Test'));
        $this->assertSame(['X-Append-Test' => ['foo/bar']], $response->getHeaders());
        $this->assertSame(['X-Append-Test' => 'foo/bar'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('X-Append-Test', 'foo/baz', false);
        $this->assertSame(true, $response->hasHeader('X-Append-Test'));
        $this->assertSame(['foo/bar', 'foo/baz'], $response->getHeader('X-Append-Test'));
        $this->assertSame('foo/bar, foo/baz', $response->getHeaderLine('X-Append-Test'));
        $this->assertSame(['X-Append-Test' => ['foo/bar', 'foo/baz']], $response->getHeaders());
        $this->assertSame(['X-Append-Test' => 'foo/bar, foo/baz'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('X-Append-Test', 'foo/baz', true);
        $this->assertSame(true, $response->hasHeader('X-Append-Test'));
        $this->assertSame(['foo/baz'], $response->getHeader('X-Append-Test'));
        $this->assertSame('foo/baz', $response->getHeaderLine('X-Append-Test'));
        $this->assertSame(['X-Append-Test' => ['foo/baz']], $response->getHeaders());
        $this->assertSame(['X-Append-Test' => 'foo/baz'], $symfony->getHttpHeaders());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSetHttpHeaderNoAppendContentType()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse();
        $this->assertSame(false, $response->hasHeader('Content-Type'));
        $this->assertSame([], $response->getHeader('Content-Type'));
        $this->assertSame([], $symfony->getHttpHeaders());

        $response = $response->withHeader('Content-Type', 'foo/bar');

        $this->assertSame(true, $response->hasHeader('Content-Type'));
        $this->assertSame(['foo/bar'], $response->getHeader('Content-Type'));
        $this->assertSame('foo/bar', $response->getHeaderLine('Content-Type'));
        $this->assertSame(['Content-Type' => ['foo/bar']], $response->getHeaders());
        $this->assertSame(['Content-Type' => 'foo/bar'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('Content-Type', 'foo/baz', false);
        $this->assertSame(true, $response->hasHeader('Content-Type'));
        $this->assertSame(['foo/bar'], $response->getHeader('Content-Type'));
        $this->assertSame('foo/bar', $response->getHeaderLine('Content-Type'));
        $this->assertSame(['Content-Type' => ['foo/bar']], $response->getHeaders());
        $this->assertSame(['Content-Type' => 'foo/bar'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('Content-Type', 'foo/baz', true);
        $this->assertSame(true, $response->hasHeader('Content-Type'));
        $this->assertSame(['foo/baz'], $response->getHeader('Content-Type'));
        $this->assertSame('foo/baz', $response->getHeaderLine('Content-Type'));
        $this->assertSame(['Content-Type' => ['foo/baz']], $response->getHeaders());
        $this->assertSame(['Content-Type' => 'foo/baz'], $symfony->getHttpHeaders());
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

        return [Response::fromSfWebReponse($symfonyResponseMock), $symfonyResponseMock];
    }
}
