<?php

namespace brnc\Symfony1\Message;


use brnc\Contract\Http\Message\HeaderInterface;

/**
 * subset of psr/http-message-implementation
 */
class Header extends HeaderReader implements HeaderInterface
{
    /** @var string[] */
    protected $headersRemoved = [];

    /** @var string[] */
    protected $headersReplaced = [];

    /**
     * @param string $version
     *
     * @return Header
     */
    public function withProtocolVersion($version)
    {
        $clone                  = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return Header
     */
    public function withHeader($name, $value)
    {
        $clone     = clone $this;
        $fieldName = strtolower($name);

        $clone->headers[$fieldName] = [
            self::HEADER_NAME    => $name,
            self::HEADER_CONTENT => is_array($value)? $value : [$value],
        ];
        unset($clone->headersRemoved[$fieldName]);
        $clone->headersReplaced[$fieldName] = true;

        return $clone;
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return Header
     */
    public function withAddedHeader($name, $value)
    {
        $clone     = clone $this;
        $fieldName = strtolower($name);
        $values    = is_array($value)? $value : [$value];

        if (isset($clone->headers[$fieldName])) {
            $clone->headers[$fieldName][self::HEADER_CONTENT] = array_merge($clone->headers[$fieldName][self::HEADER_CONTENT], $values);
        }
        else {
            $clone->headers[$fieldName] = [
                self::HEADER_NAME    => $name,
                self::HEADER_CONTENT => $values,
            ];
            unset($clone->headersRemoved[$fieldName]);
            $clone->headersReplaced[$fieldName] = true;
        }

        return $clone;
    }

    /**
     * @param string $name
     *
     * @return Header
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
