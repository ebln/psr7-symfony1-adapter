<?php

namespace brnc\Symfony1\Message\Implementation\Hidden;

trait CommonHeadTrait
{
    /** @var string */
    protected $protocolVersion = '';

    /** @var string[][]|string[][][] */
    protected $headers = [];

    /** @var string[] */
    protected $headersRemoved = [];

    /** @var string[] */
    protected $headersReplaced = [];

    /**
     * @param string[][]|string[][][] $headers
     * @param string                  $version
     */
    public function __construct(array $headers, $version = Constant::DEFAULT_HTTP_VERSION)
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
     * @param string $version
     *
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $clone                  = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * @return array|\string[][]
     */
    public function getHeaders()
    {
        return array_column($this->headers, Constant::HEADER_CONTENT, Constant::HEADER_NAME);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)][Constant::HEADER_CONTENT]);
    }

    /**
     * @param string $name
     *
     * @return array|string[]
     */
    public function getHeader($name)
    {
        $normalisedName = strtolower($name);

        return isset($this->headers[$normalisedName][Constant::HEADER_CONTENT])? $this->headers[$normalisedName][Constant::HEADER_CONTENT] : [];
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

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        $clone     = clone $this;
        $fieldName = strtolower($name);

        $clone->headers[$fieldName] = [
            Constant::HEADER_NAME    => $name,
            Constant::HEADER_CONTENT => is_array($value)? $value : [$value],
        ];
        unset($clone->headersRemoved[$fieldName]);
        $clone->headersReplaced[$fieldName] = true;

        return $clone;
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        $clone     = clone $this;
        $fieldName = strtolower($name);
        $values    = is_array($value)? $value : [$value];

        if (isset($clone->headers[$fieldName])) {
            $clone->headers[$fieldName][Constant::HEADER_CONTENT] = array_merge($clone->headers[$fieldName][Constant::HEADER_CONTENT],
                                                                            $values
            );
        }
        else {
            $clone->headers[$fieldName] = [
                Constant::HEADER_NAME    => $name,
                Constant::HEADER_CONTENT => $values,
            ];
            unset($clone->headersRemoved[$fieldName]);
            $clone->headersReplaced[$fieldName] = true;
        }

        return $clone;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function withoutHeader($name)
    {
        $fieldName = strtolower($name);

        if (!isset($this->headers[$fieldName])) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->headers[$fieldName], $clone->headersReplaced[$fieldName]);
        $clone->headersRemoved[$fieldName] = true;

        return $clone;
    }
}
