<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\ReadMinimalRequestHeadInterfaceAdapter;
use brnc\Symfony1\Message\Obligation\sfParameterHolderSubsetInterface;
use brnc\Symfony1\Message\Obligation\sfWebRequestSubsetInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Doubler\DoubleInterface;
use Prophecy\Prophecy\ObjectProphecy;

class MinimalRequestReaderTest extends TestCase
{
    /**
     * @param array  $request
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideMinimalRequestReaderData
     */
    public function testHasHeader(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadInterfaceAdapter($sfWebRequest);
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
     * @dataProvider provideMinimalRequestReaderData
     */
    public function testGetHeader(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadInterfaceAdapter($sfWebRequest);
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
     * @dataProvider provideMinimalRequestReaderData
     */
    public function testGetHeaderLine(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadInterfaceAdapter($sfWebRequest);
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
     * @dataProvider provideMinimalRequestReaderData
     */
    public function testGetHeaders(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders)
    {
        $sfWebRequest         = $this->createSfWebRequestReadOnlyMock($request['method'], $request['version'], $request['headers']);
        $minimalRequestReader = new ReadMinimalRequestHeadInterfaceAdapter($sfWebRequest);
        $this->assertSame($expectedHeaders, $minimalRequestReader->getHeaders($headerName));
    }

    /**
     * @return array
     */
    public function provideMinimalRequestReaderData()
    {
        return [
            'happy case' => [
                'request'              => [
                    'method'  => 'gEt',
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
