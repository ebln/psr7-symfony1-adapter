<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

class HeaderCookie implements CookieInterface
{
    private string $name;
    private string $value;
    private string $completeHeader;

    public function __construct(string $name, string $value, string $completeHeader)
    {
        $this->name           = $name;
        $this->value          = $value;
        $this->completeHeader = $completeHeader;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function apply(): bool
    {
        header($this->completeHeader, false);

        return !headers_sent();
    }
}
