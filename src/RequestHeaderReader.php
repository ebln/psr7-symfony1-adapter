<?php

namespace brnc\Symfony1\Message;

use brnc\Contract\Http\Message\MinimalRequestHeaderReadInterface;

/**
 * subset of psr/http-message-implementation
 */
class RequestHeaderReader extends HeaderReader implements MinimalRequestHeaderReadInterface
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
