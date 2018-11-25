<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of CommonHead and PSR7/Message
 */
trait FalseBodyTrait
{
    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getBody()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @param \Psr\Http\Message\StreamInterface $body
     * @throws Psr7SubsetException
     */
    public function withBody(\Psr\Http\Message\StreamInterface $body)
    {
        throw new Psr7SubsetException();
    }
}
