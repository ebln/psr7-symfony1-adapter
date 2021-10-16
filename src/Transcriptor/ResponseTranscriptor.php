<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor;

use brnc\Symfony1\Message\Transcriptor\Response\CookieTranscriptorInterface;
use brnc\Symfony1\Message\Transcriptor\Response\NoCookieTranscriptor;
use brnc\Symfony1\Message\Transcriptor\Response\OptionsTranscriptor;
use brnc\Symfony1\Message\Transcriptor\Response\OptionsTranscriptorInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseTranscriptor
{
    private OptionsTranscriptorInterface $optionsTranscriptor;
    private CookieTranscriptorInterface  $cookieTranscriptor;

    public function __construct(?OptionsTranscriptorInterface $optionsTranscriptor = null, ?CookieTranscriptorInterface $cookieTranscriptor = null)
    {
        $this->optionsTranscriptor = $optionsTranscriptor ?? new OptionsTranscriptor();
        $this->cookieTranscriptor  = $cookieTranscriptor ?? new NoCookieTranscriptor();
    }

    public function transcribe(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): \sfWebResponse
    {
        $sfWebResponse->setStatusCode($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());
        $this->transcribeHeaders($psrResponse, $sfWebResponse);
        $this->cookieTranscriptor->transcribeCookies($psrResponse, $sfWebResponse);
        $this->transcribeProtocol($psrResponse, $sfWebResponse);
        $this->transcribeBody($psrResponse, $sfWebResponse);

        return $sfWebResponse;
    }

    // DOES NOT remove headers set in SF response when not set or unset in PSR-7 response
    private function transcribeHeaders(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void
    {
        /** @var string $header */
        foreach (array_keys($psrResponse->getHeaders()) as $header) {
            if ('set-cookie' !== strtolower($header)) {
                $sfWebResponse->setHttpHeader($header, $psrResponse->getHeaderLine($header), true);
            }
        }
    }

    private function transcribeProtocol(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void
    {
        // transcribe protocol and possibly also other options though there should be no strong need: just use a Content-Type header
        $this->optionsTranscriptor->transcribeOptions($sfWebResponse, [
            'http_protocol' => 'HTTP/' . $psrResponse->getProtocolVersion(),
        ]);
    }

    private function transcribeBody(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void
    {
        $body = $psrResponse->getBody();
        $sfWebResponse->setContent((string)$body);
        // prevent further writes, that wouldn't make it into the transcription
        $body->close();
    }
}
