<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Factory;

use brnc\Symfony1\Message\Factory\NowFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \brnc\Symfony1\Message\Factory\NowFactory
 *
 * @internal
 */
final class NowFactoryTest extends TestCase
{
    public function testNowReturnsDateTimeImmutable(): void
    {
        $result = (new NowFactory())->now();
        self::assertInstanceOf(\DateTimeImmutable::class, $result);
    }

    public function testNowReturnsUtcTimezone(): void
    {
        $result = (new NowFactory())->now();
        self::assertSame('UTC', $result->getTimezone()->getName());
    }

    public function testNowReturnsCurrentTime(): void
    {
        $result         = (new NowFactory())->now();
        $currentUtcTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        // Allow a small difference to account for execution time
        self::assertEqualsWithDelta($currentUtcTime->getTimestamp(), $result->getTimestamp(), 2);
    }
}
