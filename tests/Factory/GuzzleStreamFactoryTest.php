<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Factory;

use brnc\Symfony1\Message\Factory\GuzzleStreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 *
 * @covers \brnc\Symfony1\Message\Factory\GuzzleStreamFactory
 */
final class GuzzleStreamFactoryTest extends TestCase
{
    /** Used by PsrHttpFactory::createResponse */
    public function testCreateStreamFromFile(): void
    {
        $factory = new GuzzleStreamFactory();
        $stream  = $factory->createStreamFromFile('php://temp', 'wb+');
        $stream->write('FOO_');
        $stream->write('BAR_');
        $stream->seek(6);
        $stream->write('Z');

        self::assertSame('_', $stream->getContents());
        $stream->rewind();
        self::assertSame('FOO_BAZ_', $stream->getContents());
        self::assertSame('', $stream->getContents());
        self::assertSame('FOO_BAZ_', (string)$stream);
    }

    public function testCreateStream(): void
    {
        $factory = new GuzzleStreamFactory();
        $stream  = $factory->createStream('Hello world…');
        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame('Hello world…', $stream->getContents());
    }

    public function testCreateStreamFromResource(): void
    {
        $factory = new GuzzleStreamFactory();
        // Create resource
        /** @phpstan-var resource $tmpFile */
        $tmpFile = tmpfile();
        self::assertIsResource($tmpFile);
        // Write to resource before stream creation
        self::assertSame(10, fwrite($tmpFile, '1234567890'));
        // Try to read back
        self::assertSame(0, fseek($tmpFile, 0));
        self::assertSame('1234567890', stream_get_contents($tmpFile));
        // Seek to 5th byte on resource
        self::assertSame(0, fseek($tmpFile, 5));
        // Create stream
        $stream = $factory->createStreamFromResource($tmpFile);
        // Write to resource after stream creation
        self::assertSame(12, fwrite($tmpFile, ' ABCDE FGHIJ'));
        // Seek to byte 3 on resource
        self::assertSame(0, fseek($tmpFile, 3));
        // Read from Stream which appears to be coupled to the resource's index
        self::assertSame('45 ABCDE FGHIJ', $stream->getContents());
        // Rewind on the steam…
        $stream->rewind();
        self::assertSame('12345 ABCDE FGHIJ', $stream->getContents());
        fclose($tmpFile);
    }
}
