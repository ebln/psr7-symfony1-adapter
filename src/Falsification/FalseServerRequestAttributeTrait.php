<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of
 *      ReadMinimalRequestHead
 *      FalseBodyTrait
 *      FalseCommonHeadTrait
 *  vs. PSR7/ServerRequest
 *  then only taking Attribute methods
 */
trait FalseServerRequestAttributeTrait
{
    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getAttributes()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param string $name
     * @param mixed  $default
     */
    public function getAttribute($name, $default = null)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param string $name
     * @param mixed  $value
     */
    public function withAttribute($name, $value)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param string $name
     */
    public function withoutAttribute($name)
    {
        throw new Psr7SubsetException();
    }
}
