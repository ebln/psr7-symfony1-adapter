<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

use Webmozart\Assert\Assert;

class TestContainer implements CookieContainerInterface
{
    /** @var array<int, string> */
    public array $reports = [];
    /** @var CookieInterface[] */
    private array $cookies;

    /** @param TestCookie[] $cookies */
    public function __construct(array $cookies)
    {
        Assert::allIsInstanceOf($cookies, TestCookie::class);
        $this->cookies = $cookies;
        foreach ($cookies as $cookie) {
            $reporter = fn (string $report): bool => !($this->reports[] = $report);
            $cookie->setReport($reporter);
        }
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }
}
