<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Compatibility;

use brnc\Symfony1\Message\Exception\InvalidTypeException;
use Webmozart\Assert\Assert as WebmozartAssert;

/**
 * @internal
 * Class compatible with Webmozart\Assert 1.x
 */
class Assert extends WebmozartAssert
{
    /**
     * @param string $message
     *
     * @psalm-pure
     *
     * @return never
     *
     * @throws InvalidTypeException
     */
    protected static function reportInvalidArgument($message): void
    {
        throw new InvalidTypeException($message);
    }
}
