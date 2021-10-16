<?php

declare(strict_types=1);
/** @noinspection PhpUnusedParameterInspection */

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

/**
 * artificial test
 *
 * @internal
 */
final class RequestReadingTest extends TestCase
{
    /**
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testHasHeader(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders): void
    {
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = Request::fromSfWebRequest($sfWebRequest);

        static::assertSame($hasHeader, $readingRequestMock->hasHeader($headerName));
    }

    /**
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetHeader(array $request, $headerName, $hasHeader, $getHeader, $getHeaderLine, $expectedHeaders): void
    {
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = Request::fromSfWebRequest($sfWebRequest);
        static::assertSame($getHeader, $readingRequestMock->getHeader($headerName));
    }

    /**
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetHeaderLine(
        array $request,
        $headerName,
        $hasHeader,
        $getHeader,
        $getHeaderLine,
        $expectedHeaders
    ): void {
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = Request::fromSfWebRequest($sfWebRequest);
        static::assertSame($getHeaderLine, $readingRequestMock->getHeaderLine($headerName));
    }

    /**
     * @param string $headerName
     * @param bool   $hasHeader
     * @param string $getHeader
     * @param string $getHeaderLine
     * @param array  $expectedHeaders
     *
     * @dataProvider provideHeaderTestData
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetHeaders(
        array $request,
        $headerName,
        $hasHeader,
        $getHeader,
        $getHeaderLine,
        $expectedHeaders
    ): void {
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = Request::fromSfWebRequest($sfWebRequest);
        static::assertSame($expectedHeaders, $readingRequestMock->getHeaders());
    }

    public function provideHeaderTestData(): array
    {
        return [
            'happy case' => [
                'request' => [
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
            'Content-Type set' => [
                'request' => [
                    'method' => '',
                    'server' => [
                        'SERVER_PROTOCOL'       => 'HTTP/1.0',
                        'CONTENT_TYPE'          => 'multipart/form-data; boundary=foobar',
                        'HTTP_X_ANOTHER_HEADER' => 'was set',
                    ],
                ],
                'test for header'      => 'Content-Type',
                'expect hasHeader'     => true,
                'expect getHeader'     => ['multipart/form-data; boundary=foobar'],
                'expect getHeaderLine' => 'multipart/form-data; boundary=foobar',
                'expect headers'       => [
                    'content-type'     => ['multipart/form-data; boundary=foobar'],
                    'x-another-header' => ['was set'],
                ],
            ],
            'Content-Type not set' => [
                'request' => [
                    'method' => '',
                    'server' => [
                        'SERVER_PROTOCOL'       => 'HTTP/1.0',
                        'HTTP_CONTENT_TYPE'     => 'BOGUS',
                        'HTTP_X_ANOTHER_HEADER' => 'was set',
                    ],
                ],
                'test for header'      => 'Content-Type',
                'expect hasHeader'     => false,
                'expect getHeader'     => [],
                'expect getHeaderLine' => '',
                'expect headers'       => [
                    'content-type'     => ['BOGUS'],
                    'x-another-header' => ['was set'],
                ],
            ],
            'Content-Type Quirk I' => [
                'request' => [
                    'method' => '',
                    'server' => [
                        'HTTP_CONTENT_TYPE' => 'BOGUS (is overridden by following)',
                        'CONTENT_TYPE'      => 'multipart/form-data; boundary=foobar',
                    ],
                ],
                'test for header'      => 'Content-Type',
                'expect hasHeader'     => true,
                'expect getHeader'     => ['multipart/form-data; boundary=foobar'],
                'expect getHeaderLine' => 'multipart/form-data; boundary=foobar',
                'expect headers'       => [
                    'content-type' => ['multipart/form-data; boundary=foobar'],
                ],
            ],
            'Content-Type Quirk II' => [
                'request' => [
                    'method' => '',
                    'server' => [
                        'CONTENT_TYPE'      => 'multipart/form-data; boundary=foobar',
                        'HTTP_CONTENT_TYPE' => 'BOGUS HEADER!',
                    ],
                ],
                'test for header'      => 'Content-Type',
                'expect hasHeader'     => true,
                'expect getHeader'     => ['multipart/form-data; boundary=foobar'],
                'expect getHeaderLine' => 'multipart/form-data; boundary=foobar',
                'expect headers'       => [
                    'content-type' => ['BOGUS HEADER!'], // while getHeader & getHeaderLine report fine
                ],
            ],
        ];
    }

    /**
     * @phpstan-param mixed $expectedVersion
     *
     * @dataProvider provideProtocolVersionData
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testGetProtocolVersion(array $request, $expectedVersion): void
    {
        $sfWebRequest = new \sfWebRequest();
        $sfWebRequest->prepare($request['method'], $request['server']);
        $readingRequestMock = Request::fromSfWebRequest($sfWebRequest);
        static::assertSame($expectedVersion, $readingRequestMock->getProtocolVersion());
    }

    public function provideProtocolVersionData(): array
    {
        return [
            'happy case' => [
                'request' => [
                    'server' => [
                        'SERVER_PROTOCOL' => 'HTTP/1.0',
                    ],
                    'method' => '',
                ],
                'expected version' => '1.0',
            ],
            'empty string → due to symfony\'s own fallback to \'\'' => [
                'request' => [
                    'server' => [
                        'SERVER_PROTOCOL' => 'HTTP/',
                    ],
                    'method' => '',
                ],
                'expected version' => '',
            ],
            'null → due to symfony\'s own fallback to \'\'' => [
                'request' => [
                    'server' => [],
                    'method' => '',
                ],
                'expected version' => '',
            ],
            'Not number dot number I → empty string: due to symfony\'s own check to \d\.\d' => [
                'request' => [
                    'method' => 'x.9',
                    'server' => [
                        'SERVER_PROTOCOL' => 'HTTP/foo bar baz',
                    ],
                ],
                'expected version' => '',
            ],
            'Not number dot number II → empty string: due to symfony\'s own check to \d\.\d' => [
                'request' => [
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
