<?php

/**
 * Minimal mock of symfony's sfEvent to enable standalone testing
 *
 * @internal
 */
class sfEvent
{
    /** @var mixed */
    protected        $value = null;
    protected string $name  = '';
    protected object $subject;
    protected array  $parameters;

    public function getName(): string
    {
        return $this->name;
    }

    public function __construct($subject, string $name, array $parameters = [])
    {
        $this->name       = $name;
        $this->subject    = $subject;
        $this->parameters = $parameters;
    }

    public function setReturnValue($value)
    {
        $this->value = $value;
    }

    public function getReturnValue()
    {
        return $this->value;
    }

    public function getSubject(): object
    {
        return $this->subject;
    }

    public function offsetGet($name)
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw new \InvalidArgumentException();
        }

        return $this->parameters[$name];
    }

    public function offsetSet($name, $value)
    {
        $this->parameters[$name] = $value;
    }
}
