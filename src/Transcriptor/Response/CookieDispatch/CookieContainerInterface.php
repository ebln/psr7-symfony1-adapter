<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

interface CookieContainerInterface
{
    /** @return CookieInterface[] */
    public function getCookies(): array;
}
