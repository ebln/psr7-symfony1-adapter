<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

interface CookieInterface
{
    public function getName(): string;

    public function getValue(): string;

    public function apply(): bool;
}
