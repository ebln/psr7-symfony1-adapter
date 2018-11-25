<?php

namespace brnc\Symfony1\Message\Implementation;

use brnc\Contract\Http\Message\ReadMinimalRequestHeadInterface;

/**
 * subset of psr/http-message-implementation
 *
 * @deprecated use SubsetRequest or SubsetServerRequest instead
 */
class ReadMinimalRequestHead extends ReadCommonHead implements ReadMinimalRequestHeadInterface
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
}
