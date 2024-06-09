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
    public function testGetName(): void
    {
        self::assertSame('test', $this->createCookie('test', 'value', [])->getName());
    }

    public function testGetValue(): void
    {
        self::assertSame('value', $this->createCookie('test', 'value', [])->getValue());
    }

    public function testConstructorAssignsOptions(): void
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

    /** @param array{expires?: int, path?: string, domain?: string, secure?: bool, httponly?: bool, samesite?: 'Lax'|'None'|'Strict'}  $options */
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
