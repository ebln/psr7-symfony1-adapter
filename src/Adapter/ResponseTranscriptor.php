<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Exception\LogicException;
use Psr\Http\Message\ResponseInterface;

class ResponseTranscriptor
{
    public static function transcript(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): \sfWebResponse
    {
        // transcribe status and reason
        $sfWebResponse->setStatusCode($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());

        // transcribe body
        $body = $psrResponse->getBody();
        $sfWebResponse->setContent($body->getContents());
        $body->close(); // prevent further writes, that wouldn't make it into the transcription

        // transcribe headers → caveat: DOES NOT remove headers set in SF response when not set or unset in PSR-7 response
        /** @var string $header */
        foreach (array_keys($psrResponse->getHeaders()) as $header) {
            if ('set-cookie' === strtolower($header)) {
                throw new LogicException('Cookie transcription has not been implemented!');
            }

            $sfWebResponse->setHttpHeader($header, $psrResponse->getHeaderLine($header), true);
        }

        // transcribe protocol
        self::setOptions($sfWebResponse, [
            'http_protocol' => 'HTTP/' . $psrResponse->getProtocolVersion(),
        ]);

        return $sfWebResponse;
    }

    /**
     * @param array<string, mixed> $options
     */
    private static function setOptions(\sfWebResponse $sfWebResponse, array $options): void
    {
        $options              = array_merge($sfWebResponse->getOptions(), $options);
        $reflexiveWebResponse = new \ReflectionObject($sfWebResponse);
        $reflexOptions        = $reflexiveWebResponse->getProperty('options');
        $reflexOptions->setAccessible(true);
        $reflexOptions->setValue($sfWebResponse, $options);
        $reflexOptions->setAccessible(false);
    }
}
