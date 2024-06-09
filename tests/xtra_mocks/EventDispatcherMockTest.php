<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\xtra_mocks;

use PHPUnit\Framework\TestCase;

/**
 * @covers \sfEventDispatcher
 *
 * @uses   \sfEvent
 *
 * @internal
 */
final class EventDispatcherMockTest extends TestCase
{
    /** @covers sfEventDispatcher::connect */
    public function testConnect(): void
    {
        $counter  = 0;
        $listener = static function ($event) use (&$counter): void {
            ++$counter;
        };
        $dispatcher = new \sfEventDispatcher();
        self::assertCount(0, $dispatcher->getListeners('test.event'));
        $dispatcher->connect('test.event', $listener);
        self::assertCount(1, $dispatcher->getListeners('test.event'));
        $dispatcher->notify(new \sfEvent($this, 'test.event'));
        $dispatcher->filter(new \sfEvent($this, 'test.event'), null);
        self::assertSame(2, $counter);
    }

    public function testNotify(): void
    {
        $event     = new \sfEvent($this, 'test.event', ['log' => 'foobar']);
        $semaphore = (object)['logger' => []];
        $listener  = static function (\sfEvent $event) use ($semaphore): string {
            $semaphore->logger[] = $event->offsetGet('log');

            return 'handled';
        };
        $dispatcher = new \sfEventDispatcher();
        $dispatcher->connect('test.event', $listener);
        $resultEvent = $dispatcher->notify($event);
        self::assertSame($resultEvent, $event);
        self::assertSame(['foobar'], $semaphore->logger);
    }

    public function testFilter(): void
    {
        $event        = new \sfEvent($this, 'test.event');
        $initialValue = 10;
        $listener     = static function ($event, $value) {
            return $value * 2;
        };
        $dispatcher = new \sfEventDispatcher();
        $dispatcher->connect('test.event', $listener);
        $filteredEvent = $dispatcher->filter($event, $initialValue);
        self::assertSame(20, $filteredEvent->getReturnValue());
    }
}
