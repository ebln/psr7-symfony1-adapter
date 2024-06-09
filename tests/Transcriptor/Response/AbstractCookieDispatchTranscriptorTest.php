<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Transcriptor\Response;

use brnc\Symfony1\Message\Transcriptor\CookieDispatcher;
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
 * @covers \brnc\Symfony1\Message\Transcriptor\CookieDispatcher
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\AbstractCookieDispatchTranscriptor
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\DispatchSubstitutor
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\TestContainer
 * @covers \brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\TestCookie
 *
 * @uses   \brnc\Symfony1\Message\Transcriptor\ResponseTranscriptor
 * @uses   \brnc\Symfony1\Message\Transcriptor\Response\OptionsTranscriptor
 * @uses   \sfWebResponse
 * @uses   \sfEventDispatcher
 * @uses   \sfEvent
 */
final class AbstractCookieDispatchTranscriptorTest extends TestCase
{
    /**
     * @param array<TestCookie>                                                                                                              $fixture
     * @param array<string,array{name: string, value: string, expire: null|int, path: string, domain: string, secure: bool, httpOnly: bool}> $expectation
     *
     * @dataProvider provideItDispatchesCookiesCases
     */
    public function testItDispatchesCookies(array $fixture, array $expectation): void
    {
        $psr7Response = new Response(203);

        $transcriptor = new class($fixture) extends AbstractCookieDispatchTranscriptor {
            public TestContainer $container;

            /** @param array<TestCookie> $cookies */
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
    public static function provideItDispatchesCookiesCases(): iterable
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

    public function testConntectIsTransparent(): void
    {
        $listener = static function ($event): void {};

        $basalDispatcher = $this->createMock(\sfEventDispatcher::class);
        $basalDispatcher->expects(self::once())->method('connect')->with('test.event', self::identicalTo($listener));
        $cookieDispatcher = new CookieDispatcher($basalDispatcher, false);
        $cookieDispatcher->connect('test.event', $listener);
    }

    public function testNotifyIsTransparentForOtherLogEvents(): void
    {
        $otherLogTransparent = new \sfEvent($this, 'application.log', [0 => 'logline']);

        $basalDispatcher = $this->createMock(\sfEventDispatcher::class);
        $basalDispatcher->expects(self::once())->method('notify')->with(self::identicalTo($otherLogTransparent));

        $cookieDispatcher = new CookieDispatcher($basalDispatcher, false);
        $cookieDispatcher->notify($otherLogTransparent);
    }

    public function testNotifyIsTransparentOtherEvents(): void
    {
        $fullyTransparent = new \sfEvent($this, 'other', ['key' => 'value']);

        $basalDispatcher = $this->createMock(\sfEventDispatcher::class);
        $basalDispatcher->expects(self::once())->method('notify')->with(self::identicalTo($fullyTransparent));

        $cookieDispatcher = new CookieDispatcher($basalDispatcher, false);
        $cookieDispatcher->notify($fullyTransparent);
    }

    public function testNotifyIsTransparentForResponseIfLoggingEnabled(): void
    {
        $basalDispatcher     = $this->createMock(\sfEventDispatcher::class);
        $otherLogTransparent = new \sfEvent(new \sfWebResponse($basalDispatcher, ['logging' => true]), 'application.log', [0 => 'logging enabled!']);
        $basalDispatcher->expects(self::once())->method('notify')->with(self::identicalTo($otherLogTransparent));

        $cookieDispatcher = new CookieDispatcher($basalDispatcher, true);
        $cookieDispatcher->notify($otherLogTransparent);
    }

    public function testNotifyIsTransparentForResponseIfLoggingDisabled(): void
    {
        $basalDispatcher     = $this->createMock(\sfEventDispatcher::class);
        $otherLogTransparent = new \sfEvent(new \sfWebResponse($basalDispatcher, ['logging' => false]), 'application.log', [0 => 'logging enabled!']);
        $basalDispatcher->expects(self::never())->method('notify');

        $cookieDispatcher = new CookieDispatcher($basalDispatcher, false);
        $cookieDispatcher->notify($otherLogTransparent);
    }

    public function testNotifySpawnsNoDispatcherIfNoDispatcher(): void
    {
        $notTransparent = new \sfEvent($this, 'default', ['key' => 'value']);
        $notTransparent->setReturnValue(-1);
        $cookieDispatcher = new CookieDispatcher(null, false);
        $cookieDispatcher->notify($notTransparent);
        self::assertSame(-1, $notTransparent->getReturnValue());
    }

    public function testNotifySpawnsDispatcherAfterOtherCallIfNoDispatcher(): void
    {
        $fullyTransparent = new \sfEvent($this, 'default', ['key' => 'value']);
        $cookieDispatcher = new CookieDispatcher(null, false);
        self::assertCount(1, $cookieDispatcher->getListeners('default')); // default listener, incrementing return value by one

        $cookieDispatcher->notify($fullyTransparent);
        self::assertSame(1, $fullyTransparent->getReturnValue());

        $cookieDispatcher->connect('default', static fn (\sfEvent $e) => $e->setReturnValue(($e->getReturnValue() ?? 0) + 10));
        $alsoTransparent = new \sfEvent($this, 'default', ['key' => 'value']);
        $cookieDispatcher->notify($alsoTransparent);
        self::assertSame(11, $alsoTransparent->getReturnValue());
    }
}
