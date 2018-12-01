<?php /** @noinspection PhpUnusedParameterInspection */

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetProxy;
use brnc\Tests\Symfony1\Message\Obligation\MockSfWebRequestSubsetTrait;
use PHPUnit\Framework\TestCase;

class RequestAdapterReadingTest extends TestCase
{
    use MockSfWebRequestSubsetTrait;

    /**
     * @param array  $request
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     */
    public function testHasHeader(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest       =
            $this->createSfWebRequestSubsetMock($request['method'], $request['version'], $request['headers']);
        $readingRequestMock = new Request($sfWebRequest);
        $this->assertSame($hasHeader, $readingRequestMock->hasHeader($headerName), 'before calling getHeaders()');
        $readingRequestMock->getHeaders();
        $this->assertSame($hasHeader, $readingRequestMock->hasHeader($headerName), 'after calling getHeaders()');
    }

    /**
     * @param array  $request
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     */
    public function testGetHeader(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest       =
            $this->createSfWebRequestSubsetMock($request['method'], $request['version'], $request['headers']);
        $readingRequestMock = new Request($sfWebRequest);
        $this->assertSame($getHeader, $readingRequestMock->getHeader($headerName), 'before calling getHeaders()');
        $readingRequestMock->getHeaders();
        $this->assertSame($getHeader, $readingRequestMock->getHeader($headerName), 'after calling getHeaders()');
    }

    /**
     * @param array  $request
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     */
    public function testGetHeaderLine(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine,
                                      $expectedHeaders
    ) {
        $sfWebRequest       =
            $this->createSfWebRequestSubsetMock($request['method'], $request['version'], $request['headers']);
        $readingRequestMock = new Request($sfWebRequest);
        $this->assertSame($getHeaderLine, $readingRequestMock->getHeaderLine($headerName),
                          'before calling getHeaders()');
        $readingRequestMock->getHeaders();
        $this->assertSame($getHeaderLine, $readingRequestMock->getHeaderLine($headerName),
                          'after calling getHeaders()');
    }

    /**
     * @param array  $request
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     */
    public function testGetHeaders(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders
    ) {
        $sfWebRequest       =
            $this->createSfWebRequestSubsetMock($request['method'], $request['version'], $request['headers']);
        $readingRequestMock = new Request($sfWebRequest);
        $this->assertSame($expectedHeaders, $readingRequestMock->getHeaders($headerName));
    }

    /**
     * NOTE: this actually belongs to an own test for sfWebRequestSubsetProxy
     *
     * @param array  $request
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     */
    public function testSfWebRequestSubsetProxyGetHeaders(array $request, $headerName, $hasHeader, $getHeader,
                                                          $getHeaderLine, $expectedHeaders
    ) {
        $sfWebRequest       =
            $this->createSfWebRequestSubsetMock($request['method'], $request['version'], $request['headers']);
        $proxy              = SfWebRequestSubsetProxy::create($sfWebRequest);
        $readingRequestMock = new Request($proxy);
        $this->assertSame($expectedHeaders, $readingRequestMock->getHeaders($headerName));
    }

    /**
     * @return array
     */
    public function provideHeaderTestData()
    {
        return [
            'happy case' => [
                'request'              => [
                    'method'  => '',
                    'version' => '1.0',
                    'headers' => [
                        'X-Test' => 'foo, bar',
                    ],
                ],
                'test for header'      => 'X-Test',
                'expect hasHeader'     => true,
                'expect getHeader'     => ['foo', 'bar'],
                'expect getHeaderLine' => 'foo, bar',
                'expect headers'       => [
                    'x-test' => ['foo', 'bar'],
                ],
            ],
        ];
    }

    /**
     * @param array $request
     * @param mixed $expectedVersion
     *
     * @dataProvider provideProtocolVersionData
     */
    public function testGetProtocolVersion(array $request, $expectedVersion)
    {
        $sfWebRequest       =
            $this->createSfWebRequestSubsetMock($request['method'], $request['version'], $request['headers']);
        $readingRequestMock = new Request($sfWebRequest);
        $this->assertSame($expectedVersion, $readingRequestMock->getProtocolVersion());
    }

    /**
     * @return array
     */
    public function provideProtocolVersionData()
    {
        return [
            'happy case'                                                                     => [
                'request'          => [
                    'version' => '1.0',
                    'method'  => '',
                    'headers' => [],
                ],
                'expected version' => '1.0',
            ],
            'empty string → due to symfony\'s own fallback to \'\''                          => [
                'request'          => [
                    'version' => '',
                    'method'  => '',
                    'headers' => [],
                ],
                'expected version' => '',
            ],
            'null → due to symfony\'s own fallback to \'\''                                  => [
                'request'          => [
                    'version' => null,
                    'method'  => '',
                    'headers' => [],
                ],
                'expected version' => '',
            ],
            'Not number dot number I → empty string: due to symfony\'s own check to \d\.\d'  => [
                'request'          => [
                    'version' => 'foo bar baz',
                    'method'  => 'x.9',
                    'headers' => [],
                ],
                'expected version' => '',
            ],
            'Not number dot number II → empty string: due to symfony\'s own check to \d\.\d' => [
                'request'          => [
                    'version' => 'foo bar baz',
                    'method'  => '5.y',
                    'headers' => [],
                ],
                'expected version' => '',
            ],
        ];
    }
}
