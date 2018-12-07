<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * faked delta of
 *      ReadMinimalRequestHead
 *      FalseBodyTrait
 *      FalseCommonHeadTrait
 *  vs. PSR7/ServerRequest
 *  then only taking file methods
 */
trait FalseServerRequestUploadTrait
{
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
}
