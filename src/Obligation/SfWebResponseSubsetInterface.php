<?php


namespace brnc\Symfony1\Message\Obligation;

/**
 *
 */
interface SfWebResponseSubsetInterface
{
    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param  string $name
     * @param  string $value
     * @param  string $expire
     * @param  string $path
     * @param  string $domain
     * @param  bool   $secure
     * @param  bool   $httpOnly
     */
    public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false,
                              $httpOnly = false
    );

    /**
     * @param string      $code
     * @param string|null $name
     */
    public function setStatusCode($code, $name = null);

    /**
     * @return string
     */
    public function getStatusText();

    /**
     * @return integer
     */
    public function getStatusCode();

    /**
     * @param string $name
     * @param string $value
     * @param bool   $replace
     *
     */
    public function setHttpHeader($name, $value, $replace = true);

    /**
     * @param  string $name
     * @param  string $default
     *
     * @return string
     */
    public function getHttpHeader($name, $default = null);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHttpHeader($name);

    /**
     * @return array
     */
    public function getCookies();

    /**
     * @return string[]
     */
    public function getHttpHeaders();
}
