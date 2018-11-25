<?php


namespace brnc\Symfony1\Message\Implementation;

use brnc\Symfony1\Message\Implementation\Hidden\Constant;

class SubsetServerRequest extends SubsetRequest
{
    /** @var array */
    protected $serverParams;

    /** @var array */
    protected $cookieParams = [];

    /** @var array */
    protected $queryParams = [];

    /** @var array|null|object */
    protected $parsedBody;

    /** @var array */
    protected $attributes = [];

    /**
     * @param string $method
     * @param array  $serverParams
     * @param array  $headers
     * @param string $version
     */
    public function __construct($method, array $serverParams, array $headers, $version = Constant::DEFAULT_HTTP_VERSION)
    {
        parent::__construct($method, $headers, $version);
        $this->serverParams = $serverParams;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @param array $cookies
     *
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone               = clone $this;
        $clone->cookieParams = $cookies;

        return $clone;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param array $query
     *
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone              = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * @return array|object|null
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @param $data
     *
     * @return static
     */
    public function withParsedBody($data)
    {
        $clone             = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $clone                    = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * @param $name
     *
     * @return static
     */
    public function withoutAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }
}
