<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response;

use Psr\Http\Message\ResponseInterface;

interface CookieTranscriptorInterface
{
    public function transcribeCookies(ResponseInterface $psrResponse, \sfWebResponse $sfWebResponse): void;
}
