<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of
 *      ReadMinimalRequestHead
 *      FalseBodyTrait
 *      FalseCommonHeadTrait
 *  vs. PSR7/ServerRequest
 *  then only taking modification and body methods, but getParsedBody()
 */
trait FalseServerRequestUploadAndModifyParametersTrait
{
    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param array $cookies
     */
    public function withCookieParams(array $cookies)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param array $query
     */
    public function withQueryParams(array $query)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     */
    public function getUploadedFiles()
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param array $uploadedFiles
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        throw new Psr7SubsetException();
    }

    /**
     * @deprecated Never implemented!
     * @throws Psr7SubsetException
     *
     * @param array $data
     */
    public function withParsedBody($data)
    {
        throw new Psr7SubsetException();
    }
}
