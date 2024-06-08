<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor;

use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\CookieContainerInterface;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\DispatchSubstitutor;

/**
 * Wraps sfEventDispatcher to fire PSR7 cookies in sfWebRequest
 *
 * There is no interface to implement and this class deliberately doesn't extend the sfEventDispatcher.
 * As there is to type coercion in the Sf1 code, that should be fine.
 */
class CookieDispatcher
{
    public const  APPLICATION_LOG  = 'application.log';
    private const LOGGING_PRIORITY = null; // For debugging: overrides event's priority, @see \sfLogger

    private ?\sfEventDispatcher $dispatcher;
    private bool                $logging;

    private ?int $headerCountdown = null;

    public function __construct(?\sfEventDispatcher $dispatcher, bool $logging)
    {
        $this->dispatcher = $dispatcher;
        $this->logging    = $logging;
    }

    /**
     * @param array{0: \sfEvent} $arguments # |array{0: \sfEvent, 1: mixed}|array{0: string}|array{0: string, 1: callable}
     *
     * @return mixed # void|false|\sfEvent|bool|callable[]
     */
    public function __call(string $name, array $arguments)
    {
        $this->dispatcher ??= new \sfEventDispatcher();

        return call_user_func_array([$this->dispatcher, $name], $arguments); // @phpstan-ignore argument.type
    }

    public function notify(\sfEvent $event): \sfEvent
    {   // We are only interested in logging events from the response, and pass-through everything else.
        if (self::APPLICATION_LOG !== $event->getName() || !$event->getSubject() instanceof \sfWebResponse) {
            return $this->passNotify($event);
        }
        // Override local logging for debug purposes
        if (null !== self::LOGGING_PRIORITY) { // @phpstan-ignore notIdentical.alwaysFalse
            $event->offsetSet('priority', self::LOGGING_PRIORITY); // Force logging
        }

        if ($this->logging) {
            $this->passNotify($event); // Notify is not expected to change the event; Sticking to in-order logging, over preserving possible return-event.
        }
        /** @var \sfWebResponse $response */
        $response = $event->getSubject();

        /** @var string $logMessage */
        $logMessage = $event->offsetGet(0);

        // There is always at least on header, as Content-Type is forced in sfWebResponseX::sendHttpHeaders
        if (str_starts_with($logMessage, 'Send header "')) {
            // initialize countdown with the number of header lines, after the very first header was sent out…
            $this->headerCountdown ??= count($response->getHttpHeaders());
            --$this->headerCountdown; // decrease right away…
            if (0 === $this->headerCountdown) { // so that we'll reach 0, after the last header was sent
                /** @var array{__psr7cookies: CookieContainerInterface} $options */
                $options = $response->getOptions();
                foreach ($options[DispatchSubstitutor::PSR_7_COOKIES]->getCookies() as $cookie) {
                    $cookie->apply();
                    if ($this->logging) {
                        $params = ["Send PSR7 cookie \"{$cookie->getName()}\": \"{$cookie->getValue()}\""];

                        if (null !== self::LOGGING_PRIORITY) { // @phpstan-ignore notIdentical.alwaysFalse
                            $params['priority'] = self::LOGGING_PRIORITY; // Force logging
                        }
                        $this->passNotify(
                            new \sfEvent($this, self::APPLICATION_LOG, $params)
                        );
                    }
                }
            }
        }

        return $event;
    }

    private function passNotify(\sfEvent $event): \sfEvent
    {
        if ($this->dispatcher) {
            return $this->dispatcher->notify($event);
        }

        return $event;
    }
}
