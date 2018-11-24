<?php

namespace brnc\Tests\Symfony1\Message\Implementation;

use brnc\Symfony1\Message\Obligation\NoSfWebRequestException;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetProxy;
use brnc\Tests\Symfony1\Message\Obligation\MockSfWebRequestSubsetTrait;
use PHPUnit\Framework\TestCase;

/**
 * test if pure mock and the proxy-wrapped mock return the same expectations
 * only testing the basic methods for now
 */
class SfWebRequestSubsetProxyTest extends TestCase
{
    use MockSfWebRequestSubsetTrait;

    public function test__construct()
    {
        $possibleRequest = $this->prophesize(SfWebRequestSubsetInterface::class)->reveal();
        /** @noinspection PhpParamsInspection */
        $this->assertInstanceOf(SfWebRequestSubsetProxy::class, SfWebRequestSubsetProxy::create($possibleRequest));
    }

    /**
     * @return array
     */
    public function getProxyTestData()
    {
        return [
            'plain'   => [
                'mock'   => ['GET', '1.0', []],
                'expect' => [
                    'method'        => 'GET',
                    'PathInfoArray' => ['SERVER_PROTOCOL' => 'HTTP/1.0'],
                    'header 1'      => ['name' => 'X-Foobarr', 'expect' => null],
                    'header 2'      => ['name' => 'No headers set', 'expect' => null],
                ],
            ],
            'headers' => [
                'mock'   => ['Post', '1.1', ['X-Foo' => 'bar', 'Authentication' => 'Basic 1337, Bearer 42']],
                'expect' => [
                    'method'        => 'Post',
                    'PathInfoArray' => [
                        'HTTP_X_FOO'          => 'bar',
                        'HTTP_AUTHENTICATION' => 'Basic 1337, Bearer 42',
                        'SERVER_PROTOCOL'     => 'HTTP/1.1',
                    ],
                    'header 1'      => ['name' => 'X-Foo', 'expect' => 'bar'],
                    'header 2'      => ['name' => 'Not-In-Set', 'expect' => null],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getProxyTestData
     *
     * @param array $mockParameter
     * @param array $expectations
     */
    public function testgetMethod(array $mockParameter, array $expectations)
    {
        $mockR = $this->createSfWebRequestSubsetMock(...$mockParameter);
        $proxy = SfWebRequestSubsetProxy::create($mockR);
        $this->assertSame($expectations['method'], $proxy->getMethod(), 'Proxy');
        $this->assertSame($expectations['method'], $mockR->getMethod(), 'Request');
    }

    /**
     * @dataProvider getProxyTestData
     *
     * @param array $mockParameter
     * @param array $expectations
     */
    public function testGetPathInfoArray(array $mockParameter, array $expectations)
    {
        $mockR = $this->createSfWebRequestSubsetMock(...$mockParameter);
        $proxy = SfWebRequestSubsetProxy::create($mockR);
        $this->assertSame($expectations['PathInfoArray'], $proxy->getPathInfoArray(), 'Proxy');
        $this->assertSame($expectations['PathInfoArray'], $mockR->getPathInfoArray(), 'Request');
    }

    /**
     * @dataProvider getProxyTestData
     *
     * @param array $mockParameter
     * @param array $expectations
     */
    public function testGetHttpHeader(array $mockParameter, array $expectations)
    {
        $mockR = $this->createSfWebRequestSubsetMock(...$mockParameter);
        $proxy = SfWebRequestSubsetProxy::create($mockR);
        $this->assertSame($expectations['header 1']['expect'], $proxy->getHttpHeader($expectations['header 1']['name']), 'Proxy');
        $this->assertSame($expectations['header 2']['expect'], $proxy->getHttpHeader($expectations['header 2']['name']), 'Proxy');
        $this->assertSame($expectations['header 1']['expect'], $mockR->getHttpHeader($expectations['header 1']['name']), 'Request');
        $this->assertSame($expectations['header 2']['expect'], $mockR->getHttpHeader($expectations['header 2']['name']), 'Request');
    }

    /**
     * @dataProvider getFailingConstructionData
     *
     * @param mixed  $argument
     * @param string $expectedMessage
     */
    public function testFailingConstructor($argument, $expectedMessage)
    {
        $this->expectException(NoSfWebRequestException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->assertInstanceOf(SfWebRequestSubsetProxy::class, SfWebRequestSubsetProxy::create($argument));
    }

    /**
     * @return array
     */
    public function getFailingConstructionData()
    {
        return [
            'null'        => [
                'argument'         => null,
                'expected message' => 'Expected sfWebRequest as argument! NULL provided.',
            ],
            '1337'        => [
                'argument'         => 1337,
                'expected message' => 'Expected sfWebRequest as argument! integer provided.',
            ],
            'stdClass'    => [
                'argument'         => (object)['foo bar'],
                'expected message' => 'Expected sfWebRequest as argument! stdClass provided.',
            ],
            'error class' => [
                'argument'         => new NoSfWebRequestException(),
                'expected message' => 'Expected sfWebRequest as argument! ' . NoSfWebRequestException::class
                                      . ' provided.',
            ],
        ];
    }
}
