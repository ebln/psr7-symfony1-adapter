<?php /** @noinspection PhpUnusedParameterInspection */

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

class RequestReadingTest extends TestCase
{
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
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = new Request($sfWebRequest);

        $this->assertSame($hasHeader, $readingRequestMock->hasHeader($headerName));
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
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = new Request($sfWebRequest);
        $this->assertSame($getHeader, $readingRequestMock->getHeader($headerName));
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
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = new Request($sfWebRequest);
        $this->assertSame($getHeaderLine, $readingRequestMock->getHeaderLine($headerName));
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
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = new Request($sfWebRequest);
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
                    'method' => '',
                    'server' => [
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                        'HTTP_X_TEST'     => 'foo, bar',
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
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
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
                    'server' => [
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                    ],
                    'method' => '',

                ],
                'expected version' => '1.0',
            ],
            'empty string → due to symfony\'s own fallback to \'\''                          => [
                'request'          => [
                    'server' => [
                        'SERVER_PROTOCOL' => 'HTTP/',
                    ],
                    'method' => '',

                ],
                'expected version' => '',
            ],
            'null → due to symfony\'s own fallback to \'\''                                  => [
                'request'          => [
                    'server' => [],
                    'method' => '',

                ],
                'expected version' => '',
            ],
            'Not number dot number I → empty string: due to symfony\'s own check to \d\.\d'  => [
                'request'          => [
                    'method' => 'x.9',
                    'server' => [
                        'SERVER_PROTOCOL' => 'HTTP/foo bar baz',
                    ],

                ],
                'expected version' => '',
            ],
            'Not number dot number II → empty string: due to symfony\'s own check to \d\.\d' => [
                'request'          => [
                    'method' => '5.y',
                    'server' => [
                        'SERVER_PROTOCOL' => 'foo bar baz',
                    ],

                ],
                'expected version' => '',
            ],
        ];
    }
}
