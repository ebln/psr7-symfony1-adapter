<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Transcriptor\Response\CookieDispatch;

use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\HeaderCookie;
use PHPUnit\Framework\TestCase;

/**
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\HeaderCookie
 *
 * @internal
 */
final class HeaderCookieTest extends TestCase
{
    private ?HeaderCookie $headerCookie = null;

    protected function setUp(): void
    {
        $this->headerCookie = new HeaderCookie('test', 'value', 'Set-Cookie: test=value; Path=/; HttpOnly');
    }

    public function testGetName()
    {
        self::assertSame('test', $this->headerCookie->getName());
    }

    public function testGetValue()
    {
        self::assertSame('value', $this->headerCookie->getValue());
    }

    public function testConstructorAssignsProperties()
    {
        $headerCookie = new HeaderCookie('name', 'value', 'Set-Cookie: name=value; Path=/; HttpOnly');

        $reflection = new \ReflectionClass($headerCookie);

        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setAccessible(true);
        self::assertSame('name', $nameProperty->getValue($headerCookie));

        $valueProperty = $reflection->getProperty('value');
        $valueProperty->setAccessible(true);
        self::assertSame('value', $valueProperty->getValue($headerCookie));

        $headerProperty = $reflection->getProperty('completeHeader');
        $headerProperty->setAccessible(true);
        self::assertSame('Set-Cookie: name=value; Path=/; HttpOnly', $headerProperty->getValue($headerCookie));
    }
}
