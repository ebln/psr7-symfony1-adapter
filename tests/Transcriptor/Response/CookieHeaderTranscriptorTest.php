<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Transcriptor\Response;

use brnc\Symfony1\Message\Transcriptor\Response\CookieHeaderTranscriptor;
use brnc\Symfony1\Message\Transcriptor\ResponseTranscriptor;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * @internal
 *
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\CookieHeaderTranscriptor
 */
final class CookieHeaderTranscriptorTest extends TestCase
{
    /**
     * @param array<int,string>                                                                                                              $fixture
     * @param array<string,array{name: string, value: string, expire: null|int, path: string, domain: string, secure: bool, httpOnly: bool}> $expectation
     *
     * @dataProvider provideCookieCases
     * @dataProvider provideMultiCookieCases
     */
    public function testTranscribeFailCookies(array $fixture, array $expectation): void
    {
        $psr7Response = new Response(203);
        foreach ($fixture as $cookie) {
            $psr7Response = $psr7Response->withAddedHeader('Set-Cookie', $cookie);
        }

        $mockResponse = $symfonyResponseMock = new \sfWebResponse(null, ['http_protocol' => 'HTTP/1.0', 'charset' => 'utf-8', 'content_type' => 'text/html', 'send_http_headers' => true]);
        $symfonyResponseMock->prepare(205, null, ['X-Preset-Header' => 'sfWebResponse'], []);
        $symfonyResponseMock->setCookie('sfWebResponseDefault', 'preset');

        $clockMock = new class() implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('1970-01-01T00:01:03', new \DateTimeZone('UTC'));
            }
        };

        $transcription = (new ResponseTranscriptor(null, new CookieHeaderTranscriptor($clockMock)))->transcribe($psr7Response, $mockResponse);

        $expectation = array_merge(
            ['sfWebResponseDefault' => ['name' => 'sfWebResponseDefault', 'value' => 'preset', 'expire' => null, 'path' => '/', 'domain' => '', 'secure' => false, 'httpOnly' => false]],
            $expectation,
        );

        self::assertSame($expectation, $transcription->getCookies());
        self::assertNull($transcription->getHttpHeader('Set-Cookie'));
    }

    /** @return array<string, array{set-cookie: string[], expectation: array<string,array{name: string, value: string, expire: null|int, path: string, domain: string, secure: bool, httpOnly: bool}>}> */
    public static function provideMultiCookieCases(): iterable
    {
        return [
            'Correctly handly multiple cookies' => [
                'set-cookie'  => [
                    'token=42; Domain=cookie.test; Path=/; HttpOnly',
                    'sessionId=abc123; Path=/; HttpOnly; SameSite=Strict',
                    'token=1337; Domain=cookie.test; Path=/; Expires=Fri, 12 Dec 2025 13:37:42 GMT; SameSite=Lax; Secure; HttpOnly',
                ],
                'expectation' => [
                    'token'     => [
                        'name'     => 'token',
                        'value'    => '1337',
                        'expire'   => 1765546662,  // Epoch timestamp for 'Fri, 12 Dec 2025 13:37:42 GMT'
                        'path'     => '/',
                        'domain'   => 'cookie.test',
                        'secure'   => true,
                        'httpOnly' => true,
                    ],
                    'sessionId' => [
                        'name'     => 'sessionId',
                        'value'    => 'abc123',
                        'expire'   => null,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => true,
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, array{set-cookie: string[], expectation: array<string,array{name: string, value: string, expire: null|int, path: string, domain: string, secure: bool, httpOnly: bool}>}> */
    public static function provideCookieCases(): iterable
    {
        return [
            'Basic Cookie'                  => [
                'set-cookie'  => ['sessionId=abc123; Path=/; HttpOnly; SameSite=Strict'],
                'expectation' => [
                    'sessionId' => [
                        'name'     => 'sessionId',
                        'value'    => 'abc123',
                        'expire'   => null,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => true,
                    ],
                ],
            ],
            'Broken Cookie value'           => [
                'set-cookie'  => ['sessionId'],
                'expectation' => [
                    'sessionId' => [
                        'name'     => 'sessionId',
                        'value'    => '',
                        'expire'   => null,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => false,
                    ],
                ],
            ],
            'Unknown Attribute'             => [
                'set-cookie'  => ['sessionId=abc123; Unknown'],
                'expectation' => [
                    'sessionId' => [
                        'name'     => 'sessionId',
                        'value'    => 'abc123',
                        'expire'   => null,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => false,
                    ],
                ],
            ],
            'Cookie with All Attributes'    => [
                'set-cookie'  => ['token=1337; Domain=cookie.test; Path=/; Expires=Fri, 12 Dec 2025 13:37:42 GMT; SameSite=Lax; Secure; HttpOnly'],
                'expectation' => [
                    'token' => [
                        'name'     => 'token',
                        'value'    => '1337',
                        'expire'   => 1765546662,  // Epoch timestamp for 'Fri, 12 Dec 2025 13:37:42 GMT'
                        'path'     => '/',
                        'domain'   => 'cookie.test',
                        'secure'   => true,
                        'httpOnly' => true,
                    ],
                ],
            ],
            'Cookie with Max-Age'           => [
                'set-cookie'  => ['userSettings=darkMode; Max-Age=3600; HttpOnly'],
                'expectation' => [
                    'userSettings' => [
                        'name'     => 'userSettings',
                        'value'    => 'darkMode',
                        'expire'   => 3663,  // 3600 seconds plus mocked epoch 63
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => true,
                    ],
                ],
            ],
            'Cookie with Max-Age < Expires' => [
                'set-cookie'  => ['maxage=LTexpires; Expires=Thu, 01 Jan 1970 01:00:10 GMT; Max-Age=' . (3600 - 63)],
                'expectation' => [
                    'maxage' => [
                        'name'     => 'maxage',
                        'value'    => 'LTexpires',
                        'expire'   => 3600,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => false,
                    ],
                ],
            ],
            'Cookie with Max-Age > Expires' => [
                'set-cookie'  => ['maxage=GTexpires; Expires=Thu, 01 Jan 1970 01:00:00 GMT; Max-Age=3610'],
                'expectation' => [
                    'maxage' => [
                        'name'     => 'maxage',
                        'value'    => 'GTexpires',
                        'expire'   => 3600,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => false,
                    ],
                ],
            ],
            'Secure Only Cookie'            => [
                'set-cookie'  => ['admin=true; Secure; HttpOnly'],
                'expectation' => [
                    'admin' => [
                        'name'     => 'admin',
                        'value'    => 'true',
                        'expire'   => null,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => true,
                        'httpOnly' => true,
                    ],
                ],
            ],
            'Cookie With Invalid Expires'   => [
                'set-cookie'  => ['debug=false; Expires=Invalid-Date; Path=/'],
                'expectation' => [
                    'debug' => [
                        'name'     => 'debug',
                        'value'    => 'false',
                        'expire'   => null, // Null due to invalid date format
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => false,
                        'httpOnly' => false,
                    ],
                ],
            ],
        ];
    }
}
