<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

abstract class AbstractCookie implements CookieInterface
{
    /** @var array{expires?: int, path?: string, domain?: string, secure?: bool, httponly?: bool, samesite?: 'Lax'|'None'|'Strict'} */
    protected array $options;

    private string  $name;
    private string  $value;

    /** @param array{expires?: int, path?: string, domain?: string, secure?: bool, httponly?: bool, samesite?: 'Lax'|'None'|'Strict'}  $options */
    public function __construct(string $name, string $value, array $options)
    {
        $this->name    = $name;
        $this->value   = $value;
        $this->options = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
