<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Factory;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Simple PSR-17 StreamFactory to enable the usage of symfony/psr-http-message-bridge
 *
 * If you want to pass your PSR-7 adapters even further to present day Symfony's http-foundation
 * you may use the following example to get a response for this PSR-7-Symfony1 adapter from the http-foundation's
 * response: (new PsrHttpFactory($unused, GuzzleStreamFactory, $unused, ResponseFactory))->createResponse()
 *
 * While you can just use (new HttpFoundationFactory())->createRequest($psrRequest) to obtain a http-foundation request
 * from this PSR-7-Symfony1 Request adapter as well as any other PSR-7 server-request.
 */
class GuzzleStreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return Utils::streamFor($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->createStreamFromResource(Utils::tryFopen($filename, $mode));
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return Utils::streamFor($resource);
    }
}
