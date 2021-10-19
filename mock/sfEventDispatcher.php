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

    public function connect(string $name, callable $listener)
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = [];
        }

        $this->listeners[$name][] = $listener;
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
}
