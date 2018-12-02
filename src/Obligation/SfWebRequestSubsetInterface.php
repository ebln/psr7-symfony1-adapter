<?php

namespace brnc\Symfony1\Message\Obligation;

/**
 * subset of sfWebRequest
 */
interface SfWebRequestSubsetInterface
{
    /**  @return  string */
    public function getMethod();

    /**  @return  array */
    public function getPathInfoArray();

    /**
     * unlike sfWebRequest a second argument for the prefix is not supported
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getHttpHeader($name);

    /**  @return  array */
    public function getOptions();

    /**  @return  array */
    public function getRequestParameters();

    /**  @return  array */
    public function getGetParameters();

    /**  @return  array */
    public function getPostParameters();

    /**
     * @param string      $name
     * @param string|null $defaultValue
     *
     * @return string|null
     */
    public function getCookie($name, $defaultValue = null);
}
