<?php

namespace brnc\Symfony1\Message\Falsification;

use brnc\Symfony1\Message\Adapter\Request as RequestAdapter;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * falsified version of @see \brnc\Symfony1\Message\Adapter\Request to achieve PSR-7 compliance by cheating
 */
class Request extends RequestAdapter implements ServerRequestInterface
{
    use FalseBodyTrait;
    use FalseServerRequestUploadTrait;

    /**
     * @deprecated Not implemented!
     * @throws Psr7SubsetException
     */
    public function getRequestTarget()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Not implemented!
     * @throws Psr7SubsetException
     */
    public function getUri()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Not implemented!
     *
     * @param mixed $requestTarget
     *
     * @throws Psr7SubsetException
     */
    public function withRequestTarget($requestTarget)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Not implemented!
     *
     * @param UriInterface $uri
     * @param bool         $preserveHost
     *
     * @throws Psr7SubsetException
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Not implemented!
     *
     * @param array $cookies
     *
     * @throws Psr7SubsetException
     */
    public function withCookieParams(array $cookies)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Not implemented!
     *
     * @param array $query
     *
     * @throws Psr7SubsetException
     */
    public function withQueryParams(array $query)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Not implemented!
     *
     * @param array|object|null $data
     *
     * @return ServerRequestInterface|void
     * @throws Psr7SubsetException
     */
    public function withParsedBody($data)
    {
        throw new Psr7SubsetException();
    }
}
