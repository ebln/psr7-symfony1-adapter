<?php

namespace brnc\Symfony1\Message\Implementation;


use brnc\Contract\Http\Message\ReadCommonHeadInterface;

/**
 * subset of psr/http-message-implementation
 *
 * this is the least general class for the moment, as there is now separate implementation for ReadHeadersInterface
 */
class ReadCommonHead implements ReadCommonHeadInterface
{
    CONST HEADER_NAME    = 'name';
    CONST HEADER_CONTENT = 'values';

    /** @var string */
    protected $protocolVersion = '';

    /** @var string[][][] */
    protected $headers = [];

    /**
     * @param string       $version
     * @param string[][][] $headers
     */
    public function __construct($version, array $headers)
    {
        $this->protocolVersion = $version;
        $this->headers         = $headers;
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @return array|\string[][]
     */
    public function getHeaders()
    {
        return array_column($this->headers, self::HEADER_CONTENT, self::HEADER_NAME);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)][self::HEADER_CONTENT]);
    }

    /**
     * @param string $name
     *
     * @return array|string[]
     */
    public function getHeader($name)
    {
        $normalisedName = strtolower($name);

        return isset($this->headers[$normalisedName][self::HEADER_CONTENT])? $this->headers[$normalisedName][self::HEADER_CONTENT] : [];
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }
}
