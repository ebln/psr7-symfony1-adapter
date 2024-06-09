<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Factory;

use brnc\Symfony1\Message\Adapter\Response;
use brnc\Symfony1\Message\Factory\ResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \brnc\Symfony1\Message\Factory\ResponseFactory
 *
 * @uses   \sfWebResponse
 * @uses   \brnc\Symfony1\Message\Adapter\Response
 */
final class ResponseFactoryTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $symfonyResponseMock = new \sfWebResponse(null, []);
        $symfonyResponseMock->prepare(200, 'Mkay', ['X-Test' => 'foobar'], [], false);
        $factory = ResponseFactory::createFactoryFromWebResponse($symfonyResponseMock);
        self::assertInstanceOf(ResponseFactory::class, $factory);

        $response = $factory->createResponse(123, 'Foo bar baZ');
        self::assertInstanceOf(ResponseFactory::class, $factory);

        self::assertInstanceOf(Response::class, $response);
        /** @phpstan-var Response $responseCopy */
        $responseCopy = $response;
        self::assertSame($symfonyResponseMock, $responseCopy->getSfWebResponse());

        self::assertSame(123, $response->getStatusCode());
        self::assertSame('Foo bar baZ', $response->getReasonPhrase());
    }
}
