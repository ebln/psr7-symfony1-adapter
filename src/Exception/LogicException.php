<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Exception;

class LogicException extends \LogicException implements AdapterThrowableInterface
{
    public static function throwAdaptingSymfony(): void
    {
        throw new self('This property cannot be altered as the underlying Symfony object does not support this.');
    }

    public static function throwPsr17Decoy(): void
    {
        throw new self('This PSR-17 HTTP Factory is just a decoy, and is NOT implementing anything!');
    }
}
