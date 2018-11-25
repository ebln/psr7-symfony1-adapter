<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of
 *      ReadMinimalRequestHead
 *      FalseBodyTrait
 *      FalseCommonHeadTrait
 *  vs. PSR7/ServerRequest
 *  then only taking read-only (non-body) parameters methods
 */
trait FalseServerRequestReadParametersTrait
{
    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getServerParams()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getCookieParams()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getQueryParams()
    {
        throw new Psr7SubsetException();
    }
}
