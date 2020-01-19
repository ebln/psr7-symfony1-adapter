<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Exception;

class InvalidTypeException extends \InvalidArgumentException implements AdapterThrowableInterface
{
    public static function throwStringExpected($value): void
    {
        throw new self('String expected, ' . gettype($value) . ' given.');
    }

    public static function throwNotEmptyExpected(): void
    {
        throw new self('Non empty value expected!');
    }

    public static function throwStringOrArrayOfStringsExpected($value): void
    {
        throw new self('String or array of strings expected, ' . gettype($value) . ' given.');
    }

    public static function throwStringOrArrayOrNullExpected($value): void
    {
        throw new self('String, array or null expected, ' . gettype($value) . ' given.');
    }
}
