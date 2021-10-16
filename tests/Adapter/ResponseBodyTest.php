<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ResponseBodyTest extends TestCase
{
    public function testWithBodyImmutable(): void
    {
        $response = $this->createResponse(false);

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);
        static::assertNotSame(spl_object_hash($response), spl_object_hash($new));
    }

    public function testWithBodyImmutableGetBody(): void
    {
        $response = $this->createResponse(false);
        static::assertSame('', $response->getBody()->getContents());
    }

    public function testWithBodyMutable(): void
    {
        $response = $this->createResponse(true);

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);
        static::assertSame(spl_object_hash($response), spl_object_hash($new), 'ONLY QUIRK!');
    }

    public function testWithBodyReadback(): void
    {
        $response = $this->createResponse(false);
        static::assertSame('', (string)$response->getBody());
        static::assertSame('', $response->getSfWebResponse()->sendContent());

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);

        static::assertSame('Hello', (string)$new->getBody());
        static::assertSame('', (string)$response->getBody(), 'Original instance must not change.');
        static::assertSame('Hello', $response->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testStreamWriting(): void
    {
        $original      = $this->createResponse(false);
        $initialStream = Utils::streamFor('FOOBAR');
        $initial       = $original->withBody($initialStream);

        static::assertSame('FOOBAR', (string)$initial->getBody());
        static::assertSame('FOOBAR', $initial->getSfWebResponse()->sendContent());

        $newStream = Utils::streamFor('Hello');
        $new       = $initial->withBody($newStream);

        static::assertSame('Hello', (string)$new->getBody());
        static::assertSame('Hello', $new->getSfWebResponse()->sendContent());

        $newStream->write(' world!');

        static::assertSame('Hello world!', (string)$new->getBody());
        static::assertSame('Hello world!', $new->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testMultipleStreamWriting(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');

        static::assertSame('foobar', (string)$responseOne->getBody());
        static::assertSame('foobar', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');

        $streamTwo   = Utils::streamFor('Hello');
        $responseTwo = $responseOne->withBody($streamTwo);
        $streamTwo->write(' world!');
        $streamOne->write('baz');

        static::assertSame('foobarbaz', (string)$responseOne->getBody());
        static::assertSame('Hello world!', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');

        static::assertSame('Hello world!', (string)$responseTwo->getBody());
        static::assertSame('Hello world!', $responseTwo->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testDeleteStream(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');
        $streamOne->close();

        static::assertSame('foo', (string)$responseOne->getBody(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
        static::assertSame('foo', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
    }

    public function testDeleteSecondStream(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');

        $streamTwo   = Utils::streamFor('Hello');
        $responseTwo = $responseOne->withBody($streamTwo);
        $streamTwo->write(' world!');
        $streamTwo->close();

        static::assertSame('foobar', $responseOne->getSfWebResponse()->sendContent(), 'Fall back to earlier stream, if the later one got closed.');
        static::assertSame('foobar', (string)$responseOne->getBody());

        static::assertSame('foobar', $responseTwo->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last readable withBody() stream wins!');
        static::assertSame('Hello', (string)$responseTwo->getBody(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
    }

    public function testDistinguishedStream(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');
        $streamTwo   = Utils::streamFor('Hello');
        $responseTwo = $responseOne->withBody($streamTwo);
        $streamTwo->write(' world!');
        $streamOne->write('baz');

        $responseOne->preSend();

        static::assertSame('foobarbaz', (string)$responseOne->getBody());
        static::assertSame('foobarbaz', $responseOne->getSfWebResponse()->sendContent(), 'Distinguished body wins.');
        static::assertSame('Hello world!', (string)$responseTwo->getBody(), 'Non-distinguished stream remains unchanged.');
        static::assertSame('foobarbaz', $responseTwo->getSfWebResponse()->sendContent(), 'Distinguished body wins.');
    }

    private function createResponse(
        bool $mutable = false
    ): Response {
        $symfonyResponseMock = new \sfWebResponse(new \sfEventDispatcher(), []);
        $symfonyResponseMock->prepare(200, 'OK', [], [], false);

        return Response::fromSfWebResponse($symfonyResponseMock, [Response::OPTION_IMMUTABLE_VIOLATION => $mutable]);
    }
}
