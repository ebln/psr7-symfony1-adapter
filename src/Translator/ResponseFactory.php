<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Translator;

use brnc\Symfony1\Message\Adapter\Response;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Pseudo- ResponseFactory to support PSR-15
 *
 * As PSR-15's signatures don't support responses as arguments,
 * this hacky factory enables you to use middlewares and handlers.
 *
 * Be aware that Responses from multiple calls to createResponse are
 *   all linked to the very same sfWebResponse this factory was constructed from!
 */
class ResponseFactory implements ResponseFactoryInterface
{
    private \sfWebResponse $sfWebResponse;

    public function __construct(\sfWebResponse $sfWebResponse)
    {
        $this->sfWebResponse = $sfWebResponse;
    }

    public static function createFactoryFromWebResponse(\sfWebResponse $sfWebResponse): self
    {
        return new self($sfWebResponse);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $response = Response::fromSfWebResponse($this->sfWebResponse);

        return $response->withStatus($code, $reasonPhrase);
    }
}
