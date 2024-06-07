<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Factory;

use Psr\Clock\ClockInterface;

class NowFactory implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
