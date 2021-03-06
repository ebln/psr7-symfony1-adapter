<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Utils;

class ResponseBodyTest extends TestCase
{

    public function testWithBodyImmutable(): void
    {
        $response = $this->createResponse(false);

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);
        $this->assertNotSame(spl_object_hash($response), spl_object_hash($new));
    }

    public function testWithBodyImmutableGetBody(): void
    {
        $response = $this->createResponse(false);
        $this->assertSame('', $response->getBody()->getContents());
    }

    public function testWithBodyMutable(): void
    {
        $response = $this->createResponse(true);

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);
        $this->assertSame(spl_object_hash($response), spl_object_hash($new), 'ONLY QUIRK!');
    }

    public function testWithBodyReadback(): void
    {
        $response = $this->createResponse(false);
        $this->assertSame('', (string)$response->getBody());
        $this->assertSame('', $response->getSfWebResponse()->sendContent());

        $newStream = Utils::streamFor('Hello');
        $new       = $response->withBody($newStream);

        $this->assertSame('Hello', (string)$new->getBody());
        $this->assertSame('', (string)$response->getBody(), 'Original instance must not change.');
        $this->assertSame('Hello', $response->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testStreamWriting(): void
    {
        $original      = $this->createResponse(false);
        $initialStream = Utils::streamFor('FOOBAR');
        $initial       = $original->withBody($initialStream);

        $this->assertSame('FOOBAR', (string)$initial->getBody());
        $this->assertSame('FOOBAR', $initial->getSfWebResponse()->sendContent());

        $newStream = Utils::streamFor('Hello');
        $new       = $initial->withBody($newStream);

        $this->assertSame('Hello', (string)$new->getBody());
        $this->assertSame('Hello', $new->getSfWebResponse()->sendContent());

        $newStream->write(' world!');

        $this->assertSame('Hello world!', (string)$new->getBody());
        $this->assertSame('Hello world!', $new->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testMultipleStreamWriting(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');

        $this->assertSame('foobar', (string)$responseOne->getBody());
        $this->assertSame('foobar', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');

        $streamTwo   = Utils::streamFor('Hello');
        $responseTwo = $responseOne->withBody($streamTwo);
        $streamTwo->write(' world!');
        $streamOne->write('baz');

        $this->assertSame('foobarbaz', (string)$responseOne->getBody());
        $this->assertSame('Hello world!', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');

        $this->assertSame('Hello world!', (string)$responseTwo->getBody());
        $this->assertSame('Hello world!', $responseTwo->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testDeleteStream(): void
    {
        $original    = $this->createResponse(false);
        $streamOne   = Utils::streamFor('foo');
        $responseOne = $original->withBody($streamOne);
        $streamOne->write('bar');
        $streamOne->close();

        $this->assertSame('foo', (string)$responseOne->getBody(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
        $this->assertSame('foo', $responseOne->getSfWebResponse()->sendContent(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
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

        $this->assertSame('foobar', $responseOne->getSfWebResponse()->sendContent(), 'Fall back to earlier stream, if the later one got closed.');
        $this->assertSame('foobar', (string)$responseOne->getBody());

        $this->assertSame('foobar', $responseTwo->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last readable withBody() stream wins!');
        $this->assertSame('Hello', (string)$responseTwo->getBody(), 'ONLY QUIRK! Preserves content of last withBody(), even when stream was closed.');
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

        $this->assertSame('foobarbaz', (string)$responseOne->getBody());
        $this->assertSame('foobarbaz', $responseOne->getSfWebResponse()->sendContent(), 'Distinguished body wins.');
        $this->assertSame('Hello world!', (string)$responseTwo->getBody(), 'Non-distinguished stream remains unchanged.');
        $this->assertSame('foobarbaz', $responseTwo->getSfWebResponse()->sendContent(), 'Distinguished body wins.');
    }

    private function createResponse(
        bool $mutable = false
    ): Response {
        $symfonyResponseMock = new \sfWebResponse(new \sfEventDispatcher(), []);
        $symfonyResponseMock->prepare(200, 'OK', [], [], false);

        return Response::fromSfWebResponse($symfonyResponseMock, [Response::OPTION_IMMUTABLE_VIOLATION => $mutable]);
    }
}
