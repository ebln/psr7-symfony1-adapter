<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

class CookieContainer
{
    /** @var CookieInterface[] */
    private array $cookies;

    /** @param CookieInterface[] $cookies */
    public function __construct(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /** @return CookieInterface[] */
    public function getCookies(): array
    {
        return $this->cookies;
    }
}
