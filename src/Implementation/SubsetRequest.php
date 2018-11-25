<?php

namespace brnc\Symfony1\Message\Implementation;

use brnc\Contract\Http\Message\ReadMinimalRequestHeadInterface;

class SubsetRequest extends CommonHead implements ReadMinimalRequestHeadInterface
{
    /** @var string */
    protected $method;

    /**
     * @param string $method
     * @param string $version
     * @param array  $headers
     */
    public function __construct($method, $version, array $headers)
    {
        parent::__construct($version, $headers);
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
