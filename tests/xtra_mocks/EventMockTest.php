<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\xtra_mocks;

use PHPUnit\Framework\TestCase;

/**
 * @covers \sfEvent
 *
 * @internal
 */
final class EventMockTest extends TestCase
{
    public function testEventProperties()
    {
        $event = new \sfEvent($this, 'test.event', ['key' => 'value']);
        self::assertSame('test.event', $event->getName());
        self::assertSame($this, $event->getSubject());
        self::assertSame('value', $event->offsetGet('key'));
    }

    public function testSetAndGetReturnValue()
    {
        $event = new \sfEvent($this, 'test.event');
        $event->setReturnValue('test');
        self::assertSame('test', $event->getReturnValue());
    }

    public function testOffsetSetAndGet()
    {
        $event = new \sfEvent($this, 'test.event', []);
        $event->offsetSet('newkey', 'newvalue');
        self::assertSame('newvalue', $event->offsetGet('newkey'));
    }

    public function testExceptionForInvalidKey()
    {
        $this->expectException(\InvalidArgumentException::class);
        $event = new \sfEvent($this, 'test.event', []);
        $dummy = $event->offsetGet('nonexistent');
    }
}
