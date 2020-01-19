<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Exception;

class LogicException extends \LogicException implements AdapterThrowableInterface
{
    public static function throwAdaptingSymfony()
    {
        throw new self('This property cannot be altered as the underlying Symfony object does not support this.');
    }
}
