<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of ReadCommonHead + FalseBodyTrait and PSR7/Message
 */
trait FalseCommonHeadTrait
{
    /**
     * @deprecated Never implemented!
     *
     * @param string $version
     *
     * @throws Psr7SubsetException
     */
    public function withProtocolVersion($version)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws Psr7SubsetException
     */
    public function withHeader($name, $value)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws Psr7SubsetException
     */
    public function withAddedHeader($name, $value)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     *
     * @param string $name
     *
     * @throws Psr7SubsetException
     */
    public function withoutHeader($name)
    {
        throw new Psr7SubsetException();
    }
}
