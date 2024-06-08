<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Transcriptor\Response;

use brnc\Symfony1\Message\Transcriptor\Response\AbstractCookieDispatchTranscriptor;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\CookieContainerInterface;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\DispatchSubstitutor;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\TestContainer;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\TestCookie;
use brnc\Symfony1\Message\Transcriptor\ResponseTranscriptor;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class AbstractCookieDispatchTranscriptorTest extends TestCase
{
    /**
     * @param array<TestCookie>                                                                                                              $fixture
     * @param array<string,array{name: string, value: string, expire: null|int, path: string, domain: string, secure: bool, httpOnly: bool}> $expectation
     *
     * @dataProvider provideTranscribeFailCookiesCases
     */
    public function testTranscribeFailCookies(array $fixture, array $expectation): void
    {
        $psr7Response = new Response(203);

        $transcriptor = new class($fixture) extends AbstractCookieDispatchTranscriptor {
            public TestContainer $container;

            /** @param array<TestCookie> $cookies*/
            public function __construct(array $cookies)
            {
                parent::__construct(new DispatchSubstitutor());

                $this->container = new TestContainer($cookies);
            }

            protected function getCookieContainer(ResponseInterface $psrResponse): CookieContainerInterface
            {
                return $this->container;
            }
        };

        $testDispatcher = new class() extends \sfEventDispatcher {
            /** @var array<array{event: string, notification: string}> */
            public array $notifications = [];

            public function notify(\sfEvent $event): \sfEvent
            {
                $this->notifications[] = ['event' => $event->getName(), 'notification' => $event->offsetGet(0)];

                return parent::notify($event);
            }
        };
        $symfonyResponseMock = new \sfWebResponse($testDispatcher, ['logging' => true, 'send_http_headers' => true]);
        $symfonyResponseMock->prepare(205, null, ['X-Preset-Header' => 'sfWebResponse'], []);
        $symfonyResponseMock->setCookie('sfWebResponseDefault', 'preset');
        $transcription = (new ResponseTranscriptor(null, $transcriptor))->transcribe($psr7Response, $symfonyResponseMock);

        $expectation = array_merge(
            ['sfWebResponseDefault' => ['name' => 'sfWebResponseDefault', 'value' => 'preset', 'expire' => null, 'path' => '/', 'domain' => '', 'secure' => false, 'httpOnly' => false]],
            $expectation,
        );

        $transcription->sendHttpHeaders();

        self::assertSame($expectation['reports'], $transcriptor->container->reports);
        self::assertSame($expectation['notifications'], $testDispatcher->notifications);
    }

    /** @return array<string, array{cookies: array<TestCookie>, expectation: array{reports: string[], notifications: array<array{event: string, notification: string}>}}> */
    public static function provideTranscribeFailCookiesCases(): iterable
    {
        return [
            'Dispatch Interception for Cookies' => [
                'cookies'     => [
                    new TestCookie('foo', 'bar'),
                    new TestCookie('omega', 'omikron'),
                ],
                'expectation' => [
                    'reports'       => [
                        'Applied Cookie: foo → bar',
                        'Applied Cookie: omega → omikron',
                    ],
                    'notifications' => [
                        [
                            'event'        => 'application.log',
                            'notification' => 'Send status "???"',
                        ],
                        [
                            'event'        => 'application.log',
                            'notification' => 'Send header "X-Preset-Header: sfWebResponse"',
                        ],
                        [
                            'event'        => 'application.log',
                            'notification' => 'Send header "Content-Type: example"',
                        ],
                        [
                            'event'        => 'application.log',
                            'notification' => 'Send PSR7 cookie "foo": "bar"',
                        ],
                        [
                            'event'        => 'application.log',
                            'notification' => 'Send PSR7 cookie "omega": "omikron"',
                        ],
                    ],
                ],
            ],
        ];
    }
}
