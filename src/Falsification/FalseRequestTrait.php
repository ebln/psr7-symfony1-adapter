<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of ReadMinimalRequestHead + FalseBodyTrait + FalseCommonHeadTrait and PSR7/Request
 */
trait FalseRequestTrait
{
    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getRequestTarget()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param mixed $requestTarget
     */
    public function withRequestTarget($requestTarget)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param string $method
     */
    public function withMethod($method)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getUri()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param \Psr\Http\Message\UriInterface $uri
     * @param bool                           $preserveHost
     */
    public function withUri(\Psr\Http\Message\UriInterface $uri, $preserveHost = false)
    {
        throw new Psr7SubsetException();
    }
}
