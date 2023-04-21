<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

/**
 * tests only edge-cases for setHttpHeader of mocked sfWebResponse
 * which has not been implicitly covered by Adapter tests
 *
 * @internal
 *
 * @coversNothing
 */
final class ResponseMockTest extends TestCase
{
    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testSetHttpHeaderAppend(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        static::assertFalse($response->hasHeader('X-Append-Test'));
        static::assertSame([], $response->getHeader('X-Append-Test'));
        static::assertSame([], $symfony->getHttpHeaders());

        $response = $response->withHeader('X-Append-Test', 'foo/bar');

        static::assertTrue($response->hasHeader('X-Append-Test'));
        static::assertSame(['foo/bar'], $response->getHeader('X-Append-Test'));
        static::assertSame('foo/bar', $response->getHeaderLine('X-Append-Test'));
        static::assertSame(['X-Append-Test' => ['foo/bar']], $response->getHeaders());
        static::assertSame(['X-Append-Test' => 'foo/bar'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('X-Append-Test', 'foo/baz', false);
        static::assertTrue($response->hasHeader('X-Append-Test'));
        static::assertSame(['foo/bar', 'foo/baz'], $response->getHeader('X-Append-Test'));
        static::assertSame('foo/bar, foo/baz', $response->getHeaderLine('X-Append-Test'));
        static::assertSame(['X-Append-Test' => ['foo/bar', 'foo/baz']], $response->getHeaders());
        static::assertSame(['X-Append-Test' => 'foo/bar, foo/baz'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('X-Append-Test', 'foo/baz', true);
        static::assertTrue($response->hasHeader('X-Append-Test'));
        static::assertSame(['foo/baz'], $response->getHeader('X-Append-Test'));
        static::assertSame('foo/baz', $response->getHeaderLine('X-Append-Test'));
        static::assertSame(['X-Append-Test' => ['foo/baz']], $response->getHeaders());
        static::assertSame(['X-Append-Test' => 'foo/baz'], $symfony->getHttpHeaders());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testSetHttpHeaderNoAppendContentType(): void
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        [$response, $symfony] = $this->createResponse();
        static::assertFalse($response->hasHeader('Content-Type'));
        static::assertSame([], $response->getHeader('Content-Type'));
        static::assertSame([], $symfony->getHttpHeaders());

        $response = $response->withHeader('Content-Type', 'foo/bar');

        static::assertTrue($response->hasHeader('Content-Type'));
        static::assertSame(['foo/bar'], $response->getHeader('Content-Type'));
        static::assertSame('foo/bar', $response->getHeaderLine('Content-Type'));
        static::assertSame(['Content-Type' => ['foo/bar']], $response->getHeaders());
        static::assertSame(['Content-Type' => 'foo/bar'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('Content-Type', 'foo/baz', false);
        static::assertTrue($response->hasHeader('Content-Type'));
        static::assertSame(['foo/bar'], $response->getHeader('Content-Type'));
        static::assertSame('foo/bar', $response->getHeaderLine('Content-Type'));
        static::assertSame(['Content-Type' => ['foo/bar']], $response->getHeaders());
        static::assertSame(['Content-Type' => 'foo/bar'], $symfony->getHttpHeaders());

        $symfony->setHttpHeader('Content-Type', 'foo/baz', true);
        static::assertTrue($response->hasHeader('Content-Type'));
        static::assertSame(['foo/baz'], $response->getHeader('Content-Type'));
        static::assertSame('foo/baz', $response->getHeaderLine('Content-Type'));
        static::assertSame(['Content-Type' => ['foo/baz']], $response->getHeaders());
        static::assertSame(['Content-Type' => 'foo/baz'], $symfony->getHttpHeaders());
    }

    /**
     * @param int                                                                                                                           $code
     * @param null|string                                                                                                                   $reasonPhrase
     * @param string[]                                                                                                                      $headers
     * @param array<string, array{name:string, value:string, expire:null|string, path:string, domain: string, secure: bool, httpOnly:bool}> $cookies
     * @param array<int|string, mixed>                                                                                                      $options
     */
    private function createResponse(
        $code = 200,
        $reasonPhrase = null,
        $headers = [],
        $cookies = [],
        array $options = []
    ): array {
        $symfonyResponseMock = new \sfWebResponse(null, $options);
        $symfonyResponseMock->prepare($code, $reasonPhrase, $headers, $cookies);

        return [Response::fromSfWebResponse($symfonyResponseMock), $symfonyResponseMock];
    }
}
