<?php /** @noinspection PhpUnusedParameterInspection */

namespace brnc\Tests\Symfony1\Message\Implementation;

use brnc\Symfony1\Message\Implementation\ReadMinimalRequestHead;
use PHPUnit\Framework\TestCase;

class ReadMinimalRequestHeadTest extends TestCase
{
    public function test__construct()
    {
        $this->assertInstanceOf(ReadMinimalRequestHead::class, new ReadMinimalRequestHead('GET', '1.1', [
            'x-test' => [
                'X-Test' => [
                    'foo',
                    'bar',
                ],
            ],
        ]));
    }

    /**
     * @dataProvider providedHeaderReaderData
     *
     * @param array    $headers
     * @param string   $testHeaderName
     * @param bool     $expectHas
     * @param string[] $expectGetHeader
     * @param string   $expectGetHeaderLine
     */
    public function testHasHeader(array $headers, $testHeaderName, $expectHas, $expectGetHeader, $expectGetHeaderLine)
    {
        $headerReader = new ReadMinimalRequestHead('POST', '0.9', $headers);
        $this->assertSame($expectHas, $headerReader->hasHeader($testHeaderName));
    }

    /**
     * @dataProvider providedHeaderReaderData
     *
     * @param array    $headers
     * @param string   $testHeaderName
     * @param bool     $expectHas
     * @param string[] $expectGetHeader
     * @param string   $expectGetHeaderLine
     */
    public function testGetHeader(array $headers, $testHeaderName, $expectHas, $expectGetHeader, $expectGetHeaderLine)
    {
        $headerReader = new ReadMinimalRequestHead('PATCH', '1.0', $headers);
        $this->assertSame($expectGetHeader, $headerReader->getHeader($testHeaderName));
    }

    /**
     * @dataProvider providedHeaderReaderData
     *
     * @param array    $headers
     * @param string   $testHeaderName
     * @param bool     $expectHas
     * @param string[] $expectGetHeader
     * @param string   $expectGetHeaderLine
     */
    public function testGetHeaderLine(array $headers, $testHeaderName, $expectHas, $expectGetHeader, $expectGetHeaderLine)
    {
        $headerReader = new ReadMinimalRequestHead('HEAD', '1.1', $headers);
        $this->assertSame($expectGetHeaderLine, $headerReader->getHeaderLine($testHeaderName));
    }

    /**
     * @return array
     */
    public function providedHeaderReaderData()
    {
        return [
            'happy case'                                                                                 => [
                'constructor headers'  => [
                    'x-test' => [
                        'name'   => 'X-Test',
                        'values' => [
                            'foo',
                            'bar',
                        ],
                    ],
                ],
                'test for header'      => 'X-Test',
                'expect hasHeader'     => true,
                'expect getHeader'     => ['foo', 'bar'],
                'expect getHeaderLine' => 'foo, bar',
            ],
            'different capitalisation'                                                                   => [
                'constructor headers'  => [
                    'x-test' => [
                        'name'   => 'X-Test',
                        'values' => [
                            'foo',
                            'bar',
                        ],
                    ],
                ],
                'test for header'      => 'x-tEsT',
                'expect hasHeader'     => true,
                'expect getHeader'     => ['foo', 'bar'],
                'expect getHeaderLine' => 'foo, bar',
            ],
            'header not found'                                                                           => [
                'constructor headers'  => [
                    'x-test' => [
                        'name'   => 'X-Test',
                        'values' => [
                            'foo',
                            'bar',
                        ],
                    ],
                ],
                'test for header'      => 'X-Foobar',
                'expect hasHeader'     => false,
                'expect getHeader'     => [],
                'expect getHeaderLine' => '',
            ],
            'Not desired - documenting quirk: header look-up fails if 1st level index is not lowercase' => [
                'constructor headers'  => [
                    'X-Test' => [
                        'name'   => 'X-Test',
                        'values' => ['foo'],
                    ],
                    [
                        'name'   => 'X-Test',
                        'values' => ['bar'],
                    ],
                ],
                'test for header'      => 'X-Test',
                'expect hasHeader'     => false,
                'expect getHeader'     => [],
                'expect getHeaderLine' => '',
            ],
        ];
    }

    public function testGetProtocolVersion()
    {
        $headerReader = new ReadMinimalRequestHead('OPTIONS', 'any string will work - no validation', []);
        $this->assertSame('any string will work - no validation', $headerReader->getProtocolVersion());
    }

    public function testGetMethod()
    {
        $headerReader = new ReadMinimalRequestHead('any string will work - no validation', '0.9',[]);
        $this->assertSame('any string will work - no validation', $headerReader->getMethod());
    }

    /**
     * @param $constructorHeaders
     * @param $expectedHeaders
     *
     * @dataProvider provideGetHeaderData
     */
    public function testGetHeaders($constructorHeaders, $expectedHeaders)
    {
        $headerReader = new ReadMinimalRequestHead('DELETE', '1.1', $constructorHeaders);
        $this->assertSame($expectedHeaders, $headerReader->getHeaders());
    }

    /**
     * @return array
     */
    public function provideGetHeaderData()
    {
        return [
            'happy case'                                                 => [
                'constructor headers' => [
                    'x-test' => [
                        'name'   => 'X-Test',
                        'values' => [
                            'foo',
                            'bar',
                        ],
                    ],
                ],
                'expected headers'    => [
                    'X-Test' => ['foo', 'bar'],
                ],
            ],
            'edge cases: not desired, but tested to monitor regressions' => [
                'constructor headers' => [
                    'x-test'    => [
                        'extra'  => (object)['fields' => 'are neglected'],
                        'name'   => 'X-Test',
                        'values' => [
                            'foo'        => 'bar',
                            'values are' => ['fully', 'transparent'],
                        ],
                    ],
                    /* 1st level index not checked for full dump */
                    'x-querty'  => [
                        'name'   => 'X-Rogue',
                        'values' => ['key'],
                    ],
                    'x-1337'    => [
                        'values' => ['nummeric index for if no name in initial header array'],
                    ],
                    /* no value → in in the dump! */
                    'just-name' => ['name' => 'JuSt-Another but no Value'],
                    [
                        'name'   => 'No first level index (implicit nummeric)',
                        'values' => ['is still dumped'],
                    ],
                    1337        => ['name'   => 'explicit nummeric index',
                                    'values' => ['is dumped, not never found by key-based methods'],
                    ],
                ],
                'expected headers'    => [
                    'X-Test'                                   => [
                        'foo'        => 'bar',
                        'values are' => ['fully', 'transparent'],
                    ],
                    'X-Rogue'                                  => ['key'],
                    0                                          => ['nummeric index for if no name in initial header array'],
                    'No first level index (implicit nummeric)' => ['is still dumped'],
                    'explicit nummeric index'                  => ['is dumped, not never found by key-based methods'],
                ],
            ],
        ];
    }
}
