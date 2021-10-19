<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Factory;

use brnc\Symfony1\Message\Adapter\Response;
use brnc\Symfony1\Message\Factory\ResponseFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ResponseFactoryTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $symfonyResponseMock = new \sfWebResponse(null, []);
        $symfonyResponseMock->prepare(200, 'Mkay', ['X-Test' => 'foobar'], [], false);
        $factory = ResponseFactory::createFactoryFromWebResponse($symfonyResponseMock);
        static::assertInstanceOf(ResponseFactory::class, $factory);

        $response = $factory->createResponse(123, 'Foo bar baZ');
        static::assertInstanceOf(ResponseFactory::class, $factory);

        static::assertInstanceOf(Response::class, $response);
        /** @phpstan-var Response $responseCopy */
        $responseCopy = $response;
        static::assertSame($symfonyResponseMock, $responseCopy->getSfWebResponse());

        static::assertSame(123, $response->getStatusCode());
        static::assertSame('Foo bar baZ', $response->getReasonPhrase());
    }
}
