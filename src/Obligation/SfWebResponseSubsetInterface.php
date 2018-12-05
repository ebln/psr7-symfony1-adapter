<?php

namespace brnc\Symfony1\Message\Obligation;

/**
 * Subset of sfWebResponse, just enough to enable self-sufficent testing
 */
interface SfWebResponseSubsetInterface
{
    /** @return int */
    public function getStatusCode();

    /** @return string */
    public function getStatusText();

    /**
     * @param string      $code
     * @param string|null $name
     */
    public function setStatusCode($code, $name = null);

    /** @return string[] */
    public function getHttpHeaders();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHttpHeader($name);

    /**
     * @param  string $name
     * @param  string $default
     *
     * @return string
     */
    public function getHttpHeader($name, $default = null);

    /**
     * @param string $name
     * @param string $value
     * @param bool   $replace
     *
     */
    public function setHttpHeader($name, $value, $replace = true);

    /** @return array */
    public function getOptions();

    /** @return array */
    public function getCookies();

    /**
     * @param  string          $name
     * @param  string          $value
     * @param  int|string|null $expire
     * @param  string          $path
     * @param  string          $domain
     * @param  bool            $secure
     * @param  bool            $httpOnly
     */
    public function setCookie(
        $name,
        $value,
        $expire = null,
        $path = '/',
        $domain = '',
        $secure = false,
        $httpOnly = false
    );
}
