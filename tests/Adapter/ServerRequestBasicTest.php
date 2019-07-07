<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use GuzzleHttp\Psr7\Stream;
use function GuzzleHttp\Psr7\stream_for;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * tests methods beyond the scope of PSR-7's Message Interface
 */
class ServerRequestBasicTest extends TestCase
{

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testStaticConstructionWithAttribute()
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET');
        $request = Request::fromSfWebRequest($symfonyRequestMock, [Request::OPTION_EXPOSE_SF_WEB_REQUEST => true]);
        $this->assertSame($symfonyRequestMock, $request->getAttribute(Request::ATTRIBUTE_SF_WEB_REQUEST));
        $this->assertSame(spl_object_hash($symfonyRequestMock), spl_object_hash($request->getAttribute(Request::ATTRIBUTE_SF_WEB_REQUEST)));
    }

    public function testBareConstructorGetBody()
    {
        $request = $this->createRequest();
        $body    = $request->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertSame('', $body->getContents(), 'Expected empty stream.');
        $this->assertSame(true, $body->isReadable(), 'Default getBody() should be readable.');
        $this->assertSame(true, $body->isWritable(), 'Default getBody() should be writable.');
    }

    public function testStaticConstructionGetBody()
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET');
        $request = Request::fromSfWebRequest($symfonyRequestMock);
        $body    = $request->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertSame('', $body->getContents(), 'Expected empty stream.');
        $this->assertSame(true, $body->isReadable(), 'Static constructed getBody() should be readable.');
        $this->assertSame(true, $body->isWritable(), 'Static constructed getBody() is writable as a quirk.');
    }

    public function testStaticConstructionGetBodyHasContent()
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET', [], [], [], [], [], 'dummy content');
        $request = Request::fromSfWebRequest($symfonyRequestMock);
        $body    = $request->getBody();
        $this->assertInstanceOf(Stream::class, $body);
        $this->assertSame('dummy content', $body->getContents(), 'Expected stream to have content.');
        $this->assertSame(true, $body->isReadable(), 'Static constructed getBody() should be readable.');
        $this->assertSame(true, $body->isWritable(), 'Static constructed getBody() is writable as a quirk.');
    }

    public function testStaticConstructionUsingPhpInputGetBody()
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET');
        $request = Request::fromSfWebRequest(
            $symfonyRequestMock,
            [Request::OPTION_BODY_USE_STREAM => true]
        );
        $body    = $request->getBody();
        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertSame('', $body->getContents(), 'Expected empty stream.');
        $this->assertSame(true, $body->isReadable(), 'Static constructed getBody() should be readable.');
        $this->assertSame(true, $body->isWritable(), 'Static constructed  getBody() is writable as a quirk.');
    }

    /**
     * @expectedException \LogicException
     */
    public function testItFailsWithBody()
    {
        $request    = $this->createRequest();
        $newRequest = $request->withBody(stream_for(''));
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetCookieParams()
    {
        $cookies = ['cookie_1' => 'asdf', 'cookie_2' => 'qwerty'];
        $_COOKIE = $cookies;
        $request = $this->createRequest();
        $this->assertSame($cookies, $request->getCookieParams(), 'Rather a quirk: returns $_COOKIE');
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetQueryParams()
    {
        $query   = ['q' => 'foo+bar', 'test' => 'true'];
        $request = $this->createRequest(null, [], $query);

        $this->assertSame($query, $request->getQueryParams());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetParsedBody()
    {
        $post    = ['user' => 'foo', 'pass' => 'bar'];
        $request = $this->createRequest(null, [], [], $post);

        $this->assertSame($post, $request->getParsedBody());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testWithAttribute()
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
    public function testWithoutAttribute()
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
    public function testGetAttribute()
    {
        $request = $this->createRequest();
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
    private function createRequest(
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
