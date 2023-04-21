<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

/**
 * tests methods beyond the scope of PSR-7's Message Interface
 *
 * @internal
 *
 * @coversNothing
 */
final class ServerRequestBasicTest extends TestCase
{
    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testGetSfWebRequest(): void
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET');
        $request = Request::fromSfWebRequest($symfonyRequestMock, []);
        static::assertSame($symfonyRequestMock, $request->getSfWebRequest());
        static::assertSame(spl_object_hash($symfonyRequestMock), spl_object_hash($request->getSfWebRequest()));
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testGetCookieParams(): void
    {
        $superCookies = $_COOKIE;
        $cookies      = ['cookie_1' => 'asdf', 'cookie_2' => 'qwerty'];
        $_COOKIE      = $cookies;
        $request      = $this->createRequest();
        static::assertSame($cookies, $request->getCookieParams(), 'Rather a quirk: returns $_COOKIE');
        $_COOKIE = $superCookies;
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testGetQueryParams(): void
    {
        $query   = ['q' => 'foo+bar', 'test' => 'true'];
        $request = $this->createRequest('GET', [], $query);

        static::assertSame($query, $request->getQueryParams());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testGetParsedBody(): void
    {
        $post    = ['user' => 'foo', 'pass' => 'bar'];
        $request = $this->createRequest('POST', [], [], $post);

        static::assertSame($post, $request->getParsedBody());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testWithAttribute(): void
    {
        $request = $this->createRequest();
        static::assertSame([], $request->getAttributes());

        $attribute = (object)['name' => 'foo', 'id' => 42, 'bar' => 'baz'];
        $request   = $request->withAttribute('Foo', $attribute);

        static::assertSame($attribute, $request->getAttribute('Foo'));
        static::assertSame(['Foo' => $attribute], $request->getAttributes());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testWithoutAttribute(): void
    {
        $request   = $this->createRequest();
        $attribute = (object)['name' => 'foo', 'id' => 42, 'bar' => 'baz'];
        $request   = $request->withAttribute('Foo', $attribute);
        $request   = $request->withAttribute('Bar', 'remains!');
        static::assertSame(['Foo' => $attribute, 'Bar' => 'remains!'], $request->getAttributes());

        $request = $request->withoutAttribute('Foo');

        static::assertNull($request->getAttribute('Foo'));
        static::assertSame(['Bar' => 'remains!'], $request->getAttributes());
    }

    /** @throws \SebastianBergmann\RecursionContext\InvalidArgumentException */
    public function testGetAttribute(): void
    {
        $request = $this->createRequest();
        $request = $request->withAttribute('Foo', 'bar');

        static::assertNull($request->getAttribute('Baz'));
        static::assertSame('bar', $request->getAttribute('Foo'));
        static::assertSame('baz', $request->getAttribute('Baz', 'baz'));
    }

    private function createRequest(
        string $method = '',
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $requestParameters = [],
        array $options = []
    ): Request {
        $symfonyRequestMock = new \sfWebRequest(null, [], [], $options);
        $symfonyRequestMock->prepare($method, $server, $get, $post, $cookie, $requestParameters);

        return Request::fromSfWebRequest($symfonyRequestMock);
    }
}
