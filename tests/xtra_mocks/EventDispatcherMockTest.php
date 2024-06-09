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
    private $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new \sfEventDispatcher();
    }

    /** @covers sfEventDispatcher::connect */
    public function testConnect()
    {
        $counter  = 0;
        $listener = static function ($event) use (&$counter): void {
            ++$counter;
        };
        $this->dispatcher->connect('test.event', $listener);
        $this->dispatcher->notify(new \sfEvent($this, 'test.event'));
        $this->dispatcher->filter(new \sfEvent($this, 'test.event'), null);
        self::assertSame(2, $counter);
    }

    public function testNotify()
    {
        $event     = new \sfEvent($this, 'test.event', ['log' => 'foobar']);
        $semaphore = (object)['logger' => []];
        $listener  = static function (\sfEvent $event) use ($semaphore): string {
            $semaphore->logger[] = $event->offsetGet('log');

            return 'handled';
        };
        $this->dispatcher->connect('test.event', $listener);
        $resultEvent = $this->dispatcher->notify($event);
        self::assertSame($resultEvent, $event);
        self::assertSame(['foobar'], $semaphore->logger);
    }

    public function testFilter()
    {
        $event        = new \sfEvent($this, 'test.event');
        $initialValue = 10;
        $listener     = static function ($event, $value) {
            return $value * 2;
        };
        $this->dispatcher->connect('test.event', $listener);
        $filteredEvent = $this->dispatcher->filter($event, $initialValue);
        self::assertSame(20, $filteredEvent->getReturnValue());
    }
}
