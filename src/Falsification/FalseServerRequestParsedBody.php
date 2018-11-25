<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of
 *      ReadMinimalRequestHead
 *      FalseBodyTrait
 *      FalseCommonHeadTrait
 *  vs. PSR7/ServerRequest
 *  then only taking getParsedBody()
 */
trait FalseServerRequestParsedBody
{
    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getParsedBody()
    {
        throw new Psr7SubsetException();
    }
}
