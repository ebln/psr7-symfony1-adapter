<?php

/**
 * Minimal mock of symfony's sfEvent to enable standalone testing
 *
 * @internal
 */
class sfEvent
{
    /** @var mixed */
    protected $value = null;
    /** @var string */
    protected $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function __construct($subject, string $name, array $parameters = [])
    {
        $this->name = $name;
    }

    public function setReturnValue($value)
    {
        $this->value = $value;
    }

    public function getReturnValue()
    {
        return $this->value;
    }
}
