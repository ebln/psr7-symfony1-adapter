<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use Psr\Http\Message\StreamInterface;

class BodyStreamHook
{
    /** @var array<string, StreamInterface> */
    private array   $bodyStreams     = [];
    private ?string $distinguishedId = null; // Holds an object identifier to the Response which content shall be used when sfWebResponse->send() is called

    public function __construct(\sfWebResponse $sfWebResponse)
    {
        $this->connect($sfWebResponse);
    }

    /** picks the specified Response's BodyStream to be send when sfWebResponse->send() or ->sendContent() is called */
    public function distinguishResponse(Response $response): void
    {
        $distinguishedId = $this->getObjectIdentifier($response);
        // unlink all other streams if one Response was marked as distinguished
        $this->bodyStreams = array_filter(
            $this->bodyStreams,
            static function (string $responseId) use ($distinguishedId) {
                return $distinguishedId === $responseId;
            },
            ARRAY_FILTER_USE_KEY
        );

        $this->distinguishedId = $distinguishedId;
    }

    public function addBodyFromResponse(Response $response): void
    {
        $this->bodyStreams[$this->getObjectIdentifier($response)] = $response->getBody();
    }

    public function processFilterContent(\sfEvent $event, ?string $value): string
    {
        // if there is a preferred stream selected always use that one and try no fallbacks
        if (null !== $this->distinguishedId) {
            return (string)$this->bodyStreams[$this->distinguishedId];
        }
        // …try the most recent readable stream
        /** @var null|StreamInterface $stream */
        foreach (array_reverse($this->bodyStreams, true) as $stream) {
            if (null !== $stream && $stream->isReadable()) {
                return (string)$stream;
            }
        }

        // …otherwise use the sfWebResponse's original content
        return (string)$value;
    }

    private function getObjectIdentifier(Response $response): string
    {
        return spl_object_hash($response);
    }

    private function connect(\sfWebResponse $sfWebResponse): void
    {
        // Use reflection
        $reflexiveWebResponse = new \ReflectionObject($sfWebResponse);
        $dispatcherRetriever  = $reflexiveWebResponse->getProperty('dispatcher');
        $dispatcherRetriever->setAccessible(true);
        /** @var null|\sfEventDispatcher $dispatcher */
        $dispatcher = $dispatcherRetriever->getValue($sfWebResponse);

        // // …or access protected properties faster than with reflection
        // // @see https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
        // $dispatcherRetriever = \Closure::bind(
        //     function & (\sfWebResponse $sfWebResponse): ?sfEventDispatcher {
        //         return $sfWebResponse->dispatcher;
        //     },
        //     null,
        //     $sfWebResponse
        // );
        // $dispatcher          = $dispatcherRetriever($sfWebResponse);

        // use response.filter_content to force update latest set Body to underlying object
        if (is_object($dispatcher) && method_exists($dispatcher, 'connect')) {
            $dispatcher->connect('response.filter_content', [$this, 'processFilterContent']);
        }
    }
}
