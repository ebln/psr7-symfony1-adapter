<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Exception;

class InvalidTypeException extends \InvalidArgumentException implements AdapterThrowableInterface
{
    /**
     * @param mixed $value
     *
     * @throws InvalidTypeException
     */
    public static function throwStringExpected($value): void
    {
        throw new self('String expected, ' . gettype($value) . ' given.');
    }

    public static function throwNotEmptyExpected(): void
    {
        throw new self('Non empty value expected!');
    }

    /**
     * @param mixed $value
     *
     * @throws InvalidTypeException
     */
    public static function throwStringOrArrayOrNullExpected($value): void
    {
        throw new self('String, array or null expected, ' . gettype($value) . ' given.');
    }
}
