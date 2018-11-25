<?php


namespace brnc\Symfony1\Message\Implementation;

use brnc\Symfony1\Message\Implementation\Hidden\Constant;

class SubsetResponse extends CommonHead
{
    /** @var int */
    protected $statusCode;

    /** @var string */
    protected $reasonPhrase = '';

    /**
     * @param int    $statusCode
     * @param string $reasonPhrase
     * @param array  $headers
     * @param string $version
     */
    public function __construct($statusCode = 200, $reasonPhrase = '', array $headers = [],
                                $version = Constant::DEFAULT_HTTP_VERSION
    )
    {
        parent::__construct($headers, $version);
        $this->statusCode   = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return static
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $clone               = clone $this;
        $clone->statusCode   = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * TODO implement RFC 7231
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }
}
