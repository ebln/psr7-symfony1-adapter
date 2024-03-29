<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Utility;

use brnc\Symfony1\Message\Exception\InvalidTypeException;
use Webmozart\Assert\Assert as WebmozartAssert;

class Assert extends WebmozartAssert
{
    /**
     * @param string $message
     *
     * @throws InvalidTypeException
     *
     * @psalm-pure
     *
     * @psalm-return never
     */
    protected static function reportInvalidArgument($message): void
    {
        throw new InvalidTypeException($message);
    }
}
