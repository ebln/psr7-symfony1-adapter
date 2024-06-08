<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response;

use brnc\Symfony1\Message\Exception\LogicException;
use Psr\Http\Message\ResponseInterface;

/**
 * Pass-through for a single `Set-Cookie` header.
 *
 * Be careful! Mind encoding, multiple cookies with the same name,
 * esp. conflicts with cookies already staged in Symfony1 (and sent via `setrawcookie()`)
 */
class SingleCookiePassTranscriptor implements CookieTranscriptorInterface
{
    public function transcribeCookies(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void
    {
        $setCookies = $psrResponse->getHeader('Set-Cookie');
        if (count($setCookies) > 1) {
            LogicException::throwCookieTranscriptionUnsupported();
        }
        $sfWebResponse->setHttpHeader('Set-Cookie', $setCookies[0], true);
    }
}
