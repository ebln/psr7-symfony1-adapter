<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\ReadMinimalRequestHeadAdapter;
use brnc\Symfony1\Message\Obligation\sfParameterHolderSubsetInterface;
use brnc\Symfony1\Message\Obligation\sfWebRequestSubsetInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Doubler\DoubleInterface;
use Prophecy\Prophecy\ObjectProphecy;

class ReadMinimalRequestHeadInterfaceAdapterTest extends TestCase
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
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadAdapter($sfWebRequest);
        $this->assertSame($hasHeader, $minimalRequestReader->hasHeader($headerName), 'before calling getHeaders()');
        $minimalRequestReader->getHeaders();
        $this->assertSame($hasHeader, $minimalRequestReader->hasHeader($headerName), 'after calling getHeaders()');
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
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadAdapter($sfWebRequest);
        $this->assertSame($getHeader, $minimalRequestReader->getHeader($headerName), 'before calling getHeaders()');
        $minimalRequestReader->getHeaders();
        $this->assertSame($getHeader, $minimalRequestReader->getHeader($headerName), 'after calling getHeaders()');
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
    public function testGetHeaderLine(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadAdapter($sfWebRequest);
        $this->assertSame($getHeaderLine, $minimalRequestReader->getHeaderLine($headerName), 'before calling getHeaders()');
        $minimalRequestReader->getHeaders();
        $this->assertSame($getHeaderLine, $minimalRequestReader->getHeaderLine($headerName), 'after calling getHeaders()');
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
    public function testGetHeaders(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadAdapter($sfWebRequest);
        $this->assertSame($expectedHeaders, $minimalRequestReader->getHeaders($headerName));
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
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadAdapter($sfWebRequest);
        $this->assertSame($expectedVersion, $minimalRequestReader->getProtocolVersion());
    }

    /**
     * @return array
     */
    public function provideProtocolVersionData()
    {
        return [
            'happy case' => [
                'request'          => [
                    'version' => '1.0',
                    'method'  => '',
                    'headers' => [],
                ],
                'expected version' => '1.0',
            ],
            'empty string → due to symfony\'s own fallback to \'\'' => [
                'request'          => [
                    'version' => '',
                    'method'  => '',
                    'headers' => [],
                ],
                'expected version' => '',
            ],
            'null → due to symfony\'s own fallback to \'\'' => [
                'request'          => [
                    'version' => null,
                    'method'  => '',
                    'headers' => [],
                ],
                'expected version' => '',
            ],
            'Not number dot number I → empty string: due to symfony\'s own check to \d\.\d' => [
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

    /**
     * @param       $method
     * @param       $version
     * @param array $headers
     *
     * @return sfWebRequestSubsetInterface|DoubleInterface
     */
    protected function createSfWebRequestReadOnlyMock($method, $version, array $headers)
    {
        /** @var sfWebRequestSubsetInterface|ObjectProphecy $request */
        $request = $this->prophesize(sfWebRequestSubsetInterface::class);

        // mock getHttpHeader
        $lowerCaseHeaders = array_combine(array_map('strtolower', array_keys($headers)), array_values($headers));
        $request->getHttpHeader(Argument::any())->willReturn(null);
        $request->getHttpHeader(Argument::type('string'))->will(function($args) use ($lowerCaseHeaders) {
            $normalizedName = strtolower($args[0]);
            /** @var string[] $headers */
            if (isset($lowerCaseHeaders[$normalizedName])) {
                return $lowerCaseHeaders[$normalizedName];
            }

            return null;
        });

        // mock getPathInfoArray() and opaque version
        $httpHeaders                    = array_combine(array_map(function($v) {
            return 'HTTP_' . str_replace('-', '_', strtoupper($v));
        }, array_keys($headers)), array_values($headers));
        $httpHeaders['SERVER_PROTOCOL'] = 'HTTP/' . $version;
        $request->getPathInfoArray()->willReturn($httpHeaders);

        // mock method()
        $request->getMethod()->willReturn($method);

        // mock now-unused other methods returning arrays
        $request->getOptions()->willReturn([]);
        $request->getRequestParameters()->willReturn([]);
        $request->getGetParameters()->willReturn([]);
        $request->getPostParameters()->willReturn([]);
        // mock now-unused other methods returning sfParameterHolder
        $attributeHolderMock = $this->prophesize(sfParameterHolderSubsetInterface::class);
        $attributeHolderMock->getAll()->willReturn([]);
        $request->getAttributeHolder()->willReturn($attributeHolderMock->reveal());
        $parameterHolderMock = $this->prophesize(sfParameterHolderSubsetInterface::class);
        $parameterHolderMock->getAll()->willReturn([]);
        $request->getParameterHolder()->willReturn($parameterHolderMock->reveal());

        return $request->reveal();
    }
}
