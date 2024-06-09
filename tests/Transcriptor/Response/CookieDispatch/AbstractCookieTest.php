<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Transcriptor\Response\CookieDispatch;

use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\AbstractCookie;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\CookieInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\AbstractCookie
 *
 * @internal
 */
final class AbstractCookieTest extends TestCase
{
    private ?CookieInterface $cookie = null;

    protected function setUp(): void
    {
        $options = [
            'expires'  => time() + 3600,
            'path'     => '/',
            'domain'   => 'example.com',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ];
        $this->cookie = $this->createCookie('test', 'value', $options);
    }

    public function testGetName()
    {
        self::assertSame('test', $this->cookie->getName());
    }

    public function testGetValue()
    {
        self::assertSame('value', $this->cookie->getValue());
    }

    public function testConstructorAssignsOptions()
    {
        $options = [
            'expires'  => time() + 3600,
            'path'     => '/',
            'domain'   => 'example.com',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ];
        $cookie = $this->createCookie('name', 'value', $options);

        $reflection = new \ReflectionClass($cookie);
        $property   = $reflection->getProperty('options');
        $property->setAccessible(true);

        self::assertSame($options, $property->getValue($cookie));
    }

    private function createCookie(string $name, string $value, array $options): CookieInterface
    {
        return new class($name, $value, $options) extends AbstractCookie {
            public function apply(): bool
            {
                return false;
            }
        };
    }
}
