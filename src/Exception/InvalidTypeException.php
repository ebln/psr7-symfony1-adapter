<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Exception;

class InvalidTypeException extends \InvalidArgumentException implements AdapterThrowableInterface
{
    public static function throwStringExpected($value)
    {
        throw new self('String expected, ' . gettype($value) . ' given.');
    }

    public static function throwStringOrArrayExpected($value)
    {
        throw new self('String or array expected, ' . gettype($value) . ' given.');
    }

    public static function throwStringOrArrayOfStringsExpected($value)
    {
        throw new self('String or array of strings expected, ' . gettype($value) . ' given.');
    }

    public static function throwStringOrArrayOrNullExpected($value)
    {
        throw new self('String, array or null expected, ' . gettype($value) . ' given.');
    }
}
