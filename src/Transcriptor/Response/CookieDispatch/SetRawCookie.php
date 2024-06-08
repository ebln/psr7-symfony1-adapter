<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

class SetRawCookie extends AbstractCookie
{
    public function apply(): bool
    {
        return setrawcookie($this->getName(), $this->getValue(), $this->options);
    }
}
