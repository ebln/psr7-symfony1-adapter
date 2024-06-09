<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Transcriptor\Response\CookieDispatch;

use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\CookieContainer;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\CookieInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\CookieContainer
 *
 * @internal
 */
final class CookieContainerTest extends TestCase
{
    public function testGetCookies(): void
    {
        $cookieMock = $this->createMock(CookieInterface::class);
        $cookies    = [$cookieMock];

        $container = new CookieContainer($cookies);
        self::assertSame($cookies, $container->getCookies());
    }

    public function testConstructorAssignsCookies(): void
    {
        $cookieMock1 = $this->createMock(CookieInterface::class);
        $cookieMock2 = $this->createMock(CookieInterface::class);
        $cookies     = [$cookieMock1, $cookieMock2];

        $container = new CookieContainer($cookies);
        self::assertCount(2, $container->getCookies());
        self::assertSame($cookieMock1, $container->getCookies()[0]);
        self::assertSame($cookieMock2, $container->getCookies()[1]);
    }
}
