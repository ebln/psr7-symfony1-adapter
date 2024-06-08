<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

class SetCookie extends AbstractCookie
{
    public function apply(): bool
    {
        return setcookie($this->getName(), $this->getValue(), $this->options);
    }
}
