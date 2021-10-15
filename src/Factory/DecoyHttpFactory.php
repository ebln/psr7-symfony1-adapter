<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Factory;

use brnc\Symfony1\Message\Exception\LogicException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Decoy PSR-17 HttpFactory to enable the usage of symfony/psr-http-message-bridge
 *
 * If you want to pass your PSR-7 adapters even further to present day Symfony's http-foundation
 * you may use the following example to get a response for this PSR-7-Symfony1 adapter from the http-foundation's response:
 *
 *    (new PsrHttpFactory(DecoyHttpFactory, GuzzleStreamFactory, DecoyHttpFactory, ResponseFactory))->createResponse()
 */
class DecoyHttpFactory implements RequestFactoryInterface, ResponseFactoryInterface, ServerRequestFactoryInterface, StreamFactoryInterface, UploadedFileFactoryInterface, UriFactoryInterface
{
    /** @psalm-suppress InvalidReturnType */
    public function createRequest(string $method, $uri): RequestInterface
    {
        LogicException::throwPsr17Decoy();
    }

    /** @psalm-suppress InvalidReturnType */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        LogicException::throwPsr17Decoy();
    }

    /** @psalm-suppress InvalidReturnType */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        LogicException::throwPsr17Decoy();
    }

    /** @psalm-suppress InvalidReturnType */
    public function createStream(string $content = ''): StreamInterface
    {
        LogicException::throwPsr17Decoy();
    }

    /** @psalm-suppress InvalidReturnType */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        LogicException::throwPsr17Decoy();
    }

    /** @psalm-suppress InvalidReturnType */
    public function createStreamFromResource($resource): StreamInterface
    {
        LogicException::throwPsr17Decoy();
    }

    /** @psalm-suppress InvalidReturnType */
    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface
    {
        LogicException::throwPsr17Decoy();
    }

    /** @psalm-suppress InvalidReturnType */
    public function createUri(string $uri = ''): UriInterface
    {
        LogicException::throwPsr17Decoy();
    }
}
