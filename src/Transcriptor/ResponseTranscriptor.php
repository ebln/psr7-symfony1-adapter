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
        // transcribe status and reason
        $sfWebResponse->setStatusCode($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());
        // transcribe protocol and possibly also other options though there should be no strong need:
        //      `content_type` & `charset` are only used if no Content-Type header was set
        //      `send_http_headers` defaults to true, and prevents headers will be sent more than once
        $sfWebResponse = $this->optionsTranscriptor->transcribeOptions($sfWebResponse, [
            'http_protocol' => 'HTTP/' . $psrResponse->getProtocolVersion(),
        ]);

        // transcribe headers
        // CAVEAT: DOES NOT remove headers set in SF response when not set or unset in PSR-7 response
        /** @var string $header */
        foreach (array_keys($psrResponse->getHeaders()) as $header) {
            if ('set-cookie' !== strtolower($header)) {
                $sfWebResponse->setHttpHeader($header, $psrResponse->getHeaderLine($header), true);
            }
        }

        // transcribe cookies
        // CAVEAT: Currently NOT SUPPORTED!
        $sfWebResponse = $this->cookieTranscriptor->transcribeCookies($psrResponse, $sfWebResponse);

        // transcribe body
        $body = $psrResponse->getBody();
        $sfWebResponse->setContent((string)$body);
        // prevent further writes, that wouldn't make it into the transcription
        // CAVEAT: likely causes exceptions if it will be written!
        $body->close();

        return $sfWebResponse;
    }
}
