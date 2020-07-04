<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

use function GuzzleHttp\Psr7\stream_for;

class ResponseBodyTest extends TestCase
{

    public function testWithBodyImmutable(): void
    {
        $response = $this->createResponse(false);

        $newStream = stream_for('Hello');
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

        $newStream = stream_for('Hello');
        $new       = $response->withBody($newStream);
        $this->assertSame(spl_object_hash($response), spl_object_hash($new), 'ONLY QUIRK!');
    }

    public function testChangeStreamAdapter(): void
    {
        $response = $this->createResponse(false);
        $this->assertSame('', (string)$response->getBody());
        $this->assertSame('', $response->getSfWebResponse()->sendContent());

        $newStream = stream_for('Hello');
        $new       = $response->withBody($newStream);

        $this->assertSame('Hello', (string)$new->getBody());
        $this->assertSame('', (string)$response->getBody(), 'Original instance must not change.');
        $this->assertSame('Hello', $response->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    public function testChangeStreamOnStream(): void
    {
        $original      = $this->createResponse(false);
        $initialStream = stream_for('FOOBAR');
        $initial       = $original->withBody($initialStream);

        $this->assertSame('FOOBAR', (string)$initial->getBody());
        $this->assertSame('FOOBAR', $initial->getSfWebResponse()->sendContent());

        $newStream = stream_for('Hello');
        $new       = $initial->withBody($newStream);

        $this->assertSame('Hello', (string)$new->getBody());
        $this->assertSame('Hello', $new->getSfWebResponse()->sendContent());

        $newStream->write(' world!');

        $this->assertSame('Hello world!', (string)$new->getBody());
        $this->assertSame('Hello world!', $new->getSfWebResponse()->sendContent(), 'ONLY QUIRK! → last withBody wins!');
    }

    private function createResponse(
        bool $mutable = false
    ): Response {
        $symfonyResponseMock = new \sfWebResponse(new \sfEventDispatcher(), []);
        $symfonyResponseMock->prepare(200, 'OK', [], [], false);

        return Response::fromSfWebResponse($symfonyResponseMock, [Response::OPTION_IMMUTABLE_VIOLATION => $mutable]);
    }
}
