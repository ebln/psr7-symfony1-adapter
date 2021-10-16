<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response;

use brnc\Symfony1\Message\Exception\LogicException;
use Psr\Http\Message\ResponseInterface;

class NoCookieTranscriptor implements CookieTranscriptorInterface
{
    public function transcribeCookies(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void
    {
        $setCookies = $psrResponse->getHeader('Set-Cookie');
        if (!empty($setCookies)) {
            LogicException::throwCookieTranscriptionUnsupported();
        }
    }
}
