<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Compatibility;

use brnc\Symfony1\Message\Exception\InvalidTypeException;
use Webmozart\Assert\Assert as WebmozartAssert;

/**
 * @internal
 * Class compatible with Webmozart\Assert 2.x
 * This automatically requires PHP ^8.2
 */
class Assert extends WebmozartAssert
{
    /**
     * @throws InvalidTypeException
     *
     * @psalm-pure
     */
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidTypeException($message);
    }
}
