<?php

namespace brnc\Symfony1\Message\Implementation;

use brnc\Contract\Http\Message\ReadMinimalRequestHeadInterface;
use brnc\Symfony1\Message\Implementation\Hidden\Constant;

class SubsetRequest extends CommonHead implements ReadMinimalRequestHeadInterface
{
    /** @var string */
    protected $method;

    /**
     * @param string $method
     * @param array  $headers
     * @param string $version
     */
    public function __construct($method, array $headers, $version = Constant::DEFAULT_HTTP_VERSION)
    {
        parent::__construct($headers, $version);
        $this->method = $method;
    }

    /** @return string */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     *
     * @return static
     */
    public function withMethod($method)
    {
        $clone         = clone $this;
        $clone->method = $method;

        return $clone;
    }
}
