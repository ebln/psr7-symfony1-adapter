<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

class ServerRequestBasicTest extends TestCase
{

    public function testConstrucWithAttribute()
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET');
        $request = new Request($symfonyRequestMock, true);
        $this->assertSame($symfonyRequestMock, $request->getAttribute(Request::ATTRIBUTE_SF_WEB_REQUEST));
        $this->assertSame(spl_object_hash($symfonyRequestMock), spl_object_hash($request->getAttribute(Request::ATTRIBUTE_SF_WEB_REQUEST)));
    }

    public function testGetCookieParams()
    {
        $cookies = ['cookie_1' => 'asdf', 'cookie_2' => 'qwerty'];
        $_COOKIE = $cookies;
        $request = $this->getRequest();
        $this->assertSame($cookies, $request->getCookieParams(), 'Rather a quirk: returns $_COOKIE');
    }

    public function testGetQueryParams()
    {
        $query   = ['q' => 'foo+bar', 'test' => 'true'];
        $request = $this->getRequest(null, [], $query);

        $this->assertSame($query, $request->getQueryParams());
    }

    public function testGetParsedBody()
    {
        $post    = ['user' => 'foo', 'pass' => 'bar'];
        $request = $this->getRequest(null, [], [], $post);

        $this->assertSame($post, $request->getParsedBody());
    }

    public function testWithAttribute()
    {
        $request = $this->getRequest();
        $this->assertSame([], $request->getAttributes());

        $attribute = (object)['name' => 'foo', 'id' => 42, 'bar' => 'baz',];
        $request   = $request->withAttribute('Foo', $attribute);

        $this->assertSame($attribute, $request->getAttribute('Foo'));
        $this->assertSame(['Foo' => $attribute], $request->getAttributes());
    }

    public function testWithoutAttribute()
    {
        $request   = $this->getRequest();
        $attribute = (object)['name' => 'foo', 'id' => 42, 'bar' => 'baz',];
        $request   = $request->withAttribute('Foo', $attribute);
        $request   = $request->withAttribute('Bar', 'remains!');
        $this->assertSame(['Foo' => $attribute, 'Bar' => 'remains!'], $request->getAttributes());

        $request = $request->withoutAttribute('Foo');

        $this->assertNull($request->getAttribute('Foo'));
        $this->assertSame(['Bar' => 'remains!'], $request->getAttributes());
    }

    public function testGetAttribute()
    {
        $request = $this->getRequest();
        $request = $request->withAttribute('Foo', 'bar');

        $this->assertNull($request->getAttribute('Baz'));
        $this->assertSame('bar', $request->getAttribute('Foo'));
        $this->assertSame('baz', $request->getAttribute('Baz', 'baz'));
    }

    /**
     * @param null  $method
     * @param array $server
     * @param array $get
     * @param array $post
     * @param array $cookie
     * @param array $requestParameters
     * @param array $options
     *
     * @return Request
     */
    private function getRequest(
        $method = null,
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $requestParameters = [],
        array $options = []
    ) {
        $symfonyRequestMock = new \sfWebRequest(null, [], [], $options);
        $symfonyRequestMock->prepare($method, $server, $get, $post, $cookie, $requestParameters);

        return new Request($symfonyRequestMock);
    }
}