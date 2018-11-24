<?php

namespace brnc\Symfony1\Message\Factory;

use brnc\Symfony1\Message\Implementation\ReadCommonHead;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface;
use brnc\Symfony1\Message\Implementation\ReadMinimalRequestHead;

/**
 * Factory for implementations of PSR-7 subsets
 *
 * @see \brnc\Symfony1\Message\Obligation\SfWebRequestSubsetProxy may be used for valid arguments
 */
class RequestAdapter
{
    /**
     * @param SfWebRequestSubsetInterface $request
     *
     * @return ReadCommonHead
     */
    public static function createHeaderReader(SfWebRequestSubsetInterface $request)
    {
        // call the proto-factory
        $arguments = ServerRequestArgument::createFromWebRequest($request);

        return new ReadCommonHead($arguments->getProtocolVersion(), $arguments->getHeaders());
    }

    /**
     * @param SfWebRequestSubsetInterface $request
     *
     * @return ReadMinimalRequestHead
     */
    public static function createRequestHeaderReader(SfWebRequestSubsetInterface $request)
    {
        // call the proto-factory
        $arguments = ServerRequestArgument::createFromWebRequest($request);

        return new ReadMinimalRequestHead($arguments->getMethod(), $arguments->getProtocolVersion(), $arguments->getHeaders());
    }
}
