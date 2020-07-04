<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

/**
 * tests methods beyond the scope of PSR-7's Message Interface
 */
class ServerRequestBasicTest extends TestCase
{
    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetSfWebRequest(): void
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET');
        $request = Request::fromSfWebRequest($symfonyRequestMock, []);
        $this->assertSame($symfonyRequestMock, $request->getSfWebRequest());
        $this->assertSame(spl_object_hash($symfonyRequestMock), spl_object_hash($request->getSfWebRequest()));
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetCookieParams(): void
    {
        $superCookies = $_COOKIE;
        $cookies      = ['cookie_1' => 'asdf', 'cookie_2' => 'qwerty'];
        $_COOKIE      = $cookies;
        $request      = $this->createRequest();
        $this->assertSame($cookies, $request->getCookieParams(), 'Rather a quirk: returns $_COOKIE');
        $_COOKIE = $superCookies;
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetQueryParams(): void
    {
        $query   = ['q' => 'foo+bar', 'test' => 'true'];
        $request = $this->createRequest('GET', [], $query);

        $this->assertSame($query, $request->getQueryParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetParsedBody(): void
    {
        $post    = ['user' => 'foo', 'pass' => 'bar'];
        $request = $this->createRequest('POST', [], [], $post);

        $this->assertSame($post, $request->getParsedBody());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithAttribute(): void
    {
        $request = $this->createRequest();
        $this->assertSame([], $request->getAttributes());

        $attribute = (object)['name' => 'foo', 'id' => 42, 'bar' => 'baz',];
        $request   = $request->withAttribute('Foo', $attribute);

        $this->assertSame($attribute, $request->getAttribute('Foo'));
        $this->assertSame(['Foo' => $attribute], $request->getAttributes());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithoutAttribute(): void
    {
        $request   = $this->createRequest();
        $attribute = (object)['name' => 'foo', 'id' => 42, 'bar' => 'baz',];
        $request   = $request->withAttribute('Foo', $attribute);
        $request   = $request->withAttribute('Bar', 'remains!');
        $this->assertSame(['Foo' => $attribute, 'Bar' => 'remains!'], $request->getAttributes());

        $request = $request->withoutAttribute('Foo');

        $this->assertNull($request->getAttribute('Foo'));
        $this->assertSame(['Bar' => 'remains!'], $request->getAttributes());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetAttribute(): void
    {
        $request = $this->createRequest();
        $request = $request->withAttribute('Foo', 'bar');

        $this->assertNull($request->getAttribute('Baz'));
        $this->assertSame('bar', $request->getAttribute('Foo'));
        $this->assertSame('baz', $request->getAttribute('Baz', 'baz'));
    }

    /**
     * @param string $method
     * @param array  $server
     * @param array  $get
     * @param array  $post
     * @param array  $cookie
     * @param array  $requestParameters
     * @param array  $options
     *
     * @return Request
     */
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
