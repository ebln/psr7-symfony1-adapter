<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use Psr\Http\Message\StreamInterface;
use ReflectionObject;

class BodyStreamHook
{
    /** @var StreamInterface[] */
    private $bodyStreams = [];
    /** @var bool */
    private $isConnected = false;

    public function __construct(\sfWebResponse $sfWebResponse)
    {
        $this->connect($sfWebResponse);
    }

    public function addStream(StreamInterface $stream): void
    {
        $this->bodyStreams[] = $stream;
    }

    public function processFilterContent(\sfEvent $event, ?string $value): string
    {
        // $trace = debug_backtrace()[2];
        // var_dump([$trace['file'] . ' → ' . $trace['line'], array_map(function($s) {return (string)$s;},$this->bodySteams)]);

        /** @var null|StreamInterface $stream */
        foreach (array_reverse($this->bodyStreams, true) as $stream) {
            if (null !== $stream && $stream->isReadable()) {
                return (string)$stream;
            }
        }

        return (string)$value;
    }

    private function connect(\sfWebResponse $sfWebResponse): void
    {
        if ($this->isConnected) {
            return;
        }

        // Use reflection
        $reflexiveWebResponse = new ReflectionObject($sfWebResponse);
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
            $this->isConnected = true;
        }
    }
}
