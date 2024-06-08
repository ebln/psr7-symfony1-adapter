<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response;

use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\CookieContainerInterface;
use brnc\Symfony1\Message\Transcriptor\Response\CookieDispatch\DispatchSubstitutor;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCookieDispatchTranscriptor implements CookieTranscriptorInterface
{
    private DispatchSubstitutor $substitutor;

    public function __construct(DispatchSubstitutor $substitutor)
    {
        $this->substitutor = $substitutor;
    }

    final public function transcribeCookies(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void
    {
        $this->substitutor->wrapDispatcher($sfWebResponse, $this->getCookieContainer($psrResponse));
    }

    /** Implement this method to obtain a CookieContainer from your PSR-7 Response! */
    abstract protected function getCookieContainer(ResponseInterface $psrResponse): CookieContainerInterface;
}
