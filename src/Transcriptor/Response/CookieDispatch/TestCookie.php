<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

class TestCookie implements CookieInterface
{
    private string $name;
    private string $value;

    /** @var ?callable */
    private $reportFn;

    public function __construct(string $name, string $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setReport(callable $reportFn): void
    {
        if (null !== $this->reportFn) {
            throw new \RuntimeException('Report already set!');
        }
        $this->reportFn = $reportFn;
    }

    public function apply(): bool
    {
        if (null === $this->reportFn) {
            throw new \RuntimeException('Report not set!');
        }

        ($this->reportFn)("Applied Cookie: {$this->name} → {$this->value}");

        return true;
    }
}
