<?php

/**
 * Minimal mock of symfony's sfEventDispatcher to enable standalone testing
 *
 * @internal
 */
class sfEventDispatcher
{
    /** @var callable[][] */
    private $listeners = [];

    public function __construct()
    {
        $this->listeners['default'] = [fn(\sfEvent $e) => $e->setReturnValue(($e->getReturnValue() ?? 0) + 1)];
    }

    public function connect(string $name, callable $listener)
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = [];
        }

        $this->listeners[$name][] = $listener;
    }

    public function getListeners(string $name): array
    {
        return $this->listeners[$name] ?? [];
    }

    public function filter(sfEvent $event, $value): sfEvent
    {
        $eventName = $event->getName();

        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            $value = call_user_func_array($listener, [$event, $value]);
        }

        $event->setReturnValue($value);

        return $event;
    }

    public function notify(sfEvent $event): sfEvent
    {
        foreach ($this->listeners[$event->getName()] ?? [] as $listener) {
            call_user_func($listener, $event);
        }

        return $event;
    }
}
