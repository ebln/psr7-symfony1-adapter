<?php

namespace brnc\Symfony1\Message\Factory;

use brnc\Symfony1\Message\ReadCommonHead;
use brnc\Symfony1\Message\Obligation\sfWebRequestSubsetInterface;
use brnc\Symfony1\Message\ReadMinimalRequestHead;

class RequestAdapter
{
    /**
     * @param sfWebRequestSubsetInterface $request
     *
     * @return ReadCommonHead
     */
    public static function createHeaderReader(sfWebRequestSubsetInterface $request)
    {
        // call the proto-factory
        $arguments = ServerRequestArgument::createFromWebRequest($request);

        return new ReadCommonHead($arguments->getProtocolVersion(), $arguments->getHeaders());
    }

    /**
     * @param sfWebRequestSubsetInterface $request
     *
     * @return ReadMinimalRequestHead
     */
    public static function createRequestHeaderReader(sfWebRequestSubsetInterface $request)
    {
        // call the proto-factory
        $arguments = ServerRequestArgument::createFromWebRequest($request);

        return new ReadMinimalRequestHead($arguments->getMethod(), $arguments->getProtocolVersion(), $arguments->getHeaders());
    }
}
