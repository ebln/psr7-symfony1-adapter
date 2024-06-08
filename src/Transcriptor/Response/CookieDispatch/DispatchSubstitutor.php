<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch;

use brnc\Symfony1\Message\Transcriptor\CookieDispatcher;

class DispatchSubstitutor
{
    public const PSR_7_COOKIES = '__psr7cookies';

    public function wrapDispatcher(\sfWebResponse $sfWebResponse, CookieContainerInterface $cookieContainer): void
    {
        /** @var array{logging: null|bool|int} $responseOptions */
        $responseOptions = $sfWebResponse->getOptions();
        // backup logging-state of the sfWebResponse, to inject that later into the wrapped Dispatcher
        $hasLogging = (bool)($responseOptions['logging'] ?? false);
        // Doing reflection magic… -.-
        $reflexiveWebResponse = new \ReflectionObject($sfWebResponse);
        // Override enable logging in sfWebResponse!
        // Attach PSR7 cookies container to sfWebResponse's `options` property.
        $optionsOverride = array_merge($responseOptions, ['logging' => true, self::PSR_7_COOKIES => $cookieContainer]);
        $reflexOptions   = $reflexiveWebResponse->getProperty('options');
        $reflexOptions->setAccessible(true);
        $reflexOptions->setValue($sfWebResponse, $optionsOverride);
        $reflexOptions->setAccessible(false);
        // Get the originally attached sfEventDispatcher
        $reflexDispatcher = $reflexiveWebResponse->getProperty('dispatcher');
        $reflexDispatcher->setAccessible(true);
        /** @var null|\sfEventDispatcher $dispatcher */
        $dispatcher = $reflexDispatcher->getValue($sfWebResponse);
        // Replace the sfEventDispatcher, with the CookieDispatcher with wraps the original one
        $reflexDispatcher->setValue($sfWebResponse, new CookieDispatcher($dispatcher, $hasLogging));
        $reflexDispatcher->setAccessible(false);
    }
}
