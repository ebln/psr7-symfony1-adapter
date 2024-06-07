<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ResponseBodyTest extends TestCase
{
    public function testWithBodyImmutable(): void
    {
        $response = $this->createResponse(false);

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);
        self::assertNotSame(spl_object_hash($response), spl_object_hash($new));
    }

    public function testWithBodyImmutableGetBody(): void
    {
        $response = $this->createResponse(false);
        self::assertSame('', $response->getBody()->getContents());
    }

    public function testWithBodyMutable(): void
    {
        $response = $this->createResponse(true);

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);
        self::assertSame(spl_object_hash($response), spl_object_hash($new), 'ONLY QUIRK!');
    }

    public function testWithBodyReadback(): void
    {
        $response = $this->createResponse(false);
        self::assertSame('', (string)$response->getBody());
        self::assertSame('', $response->getSfWebResponse()->sendContent());

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);

        self::assertSame('Hello', (string)$new->getBody());
        self::assertSame('', (string)$response->getBody(), 'Original instance must not change.');
        self::assertSame('Hello', $response->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testStreamWriting(): void
    {
        $original      = $this->createResponse(false);
        $initialStream = Utils::streamFor('FOOBAR');
        $initial       = $original->withBody($initialStream);

        self::assertSame('FOOBAR', (string)$initial->getBody());
        self::assertSame('FOOBAR', $initial->getSfWebResponse()->sendContent());

        $newStream = Utils::streamFor('Hello');
        $new       = $initial->withBody($newStream);

        self::assertSame('Hello', (string)$new->getBody());
        self::assertSame('Hello', $new->getSfWebResponse()->sendContent());

        $newStream->write(' world!');

        self::assertSame('Hello world!', (string)$new->getBody());
        self::assertSame('Hello world!', $new->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testMultipleStreamWriting(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');

        self::assertSame('foobar', (string)$responseOne->getBody());
        self::assertSame('foobar', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');

        $streamTwo   = Utils::streamFor('Hello');
        $responseTwo = $responseOne->withBody($streamTwo);
        $streamTwo->write(' world!');
        $streamOne->write('baz');

        self::assertSame('foobarbaz', (string)$responseOne->getBody());
        self::assertSame('Hello world!', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');

        self::assertSame('Hello world!', (string)$responseTwo->getBody());
        self::assertSame('Hello world!', $responseTwo->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testDeleteStream(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');
        $streamOne->close();

        self::assertSame('foo', (string)$responseOne->getBody(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
        self::assertSame('foo', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
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

        self::assertSame('foobar', $responseOne->getSfWebResponse()->sendContent(), 'Fall back to earlier stream, if the later one got closed.');
        self::assertSame('foobar', (string)$responseOne->getBody());

        self::assertSame('foobar', $responseTwo->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last readable withBody() stream wins!');
        self::assertSame('Hello', (string)$responseTwo->getBody(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
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

        self::assertSame('foobarbaz', (string)$responseOne->getBody());
        self::assertSame('foobarbaz', $responseOne->getSfWebResponse()->sendContent(), 'Distinguished body wins.');
        self::assertSame('Hello world!', (string)$responseTwo->getBody(), 'Non-distinguished stream remains unchanged.');
        self::assertSame('foobarbaz', $responseTwo->getSfWebResponse()->sendContent(), 'Distinguished body wins.');
    }

    private function createResponse(
        bool $mutable = false
    ): Response {
        $symfonyResponseMock = new \sfWebResponse(new \sfEventDispatcher(), []);
        $symfonyResponseMock->prepare(200, 'OK', [], [], false);

        return Response::fromSfWebResponse($symfonyResponseMock, [Response::OPTION_IMMUTABLE_VIOLATION => $mutable]);
    }
}
