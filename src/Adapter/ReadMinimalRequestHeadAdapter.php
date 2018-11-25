<?php

namespace brnc\Symfony1\Message\Adapter;

use brnc\Contract\Http\Message\ReadMinimalRequestHeadInterface;
use brnc\Symfony1\Message\Factory\RequestAdapter;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface;
use brnc\Symfony1\Message\Implementation\ReadMinimalRequestHead;

/**
 * Limited subject read-only Adapter/Proxy for sfWebRequest objects
 */
class ReadMinimalRequestHeadAdapter implements ReadMinimalRequestHeadInterface
{
    /** @var SfWebRequestSubsetInterface */
    protected $request;

    /** @var ReadMinimalRequestHead */
    protected $headerReader;

    /**
     * @param SfWebRequestSubsetInterface $request
     */
    public function __construct(SfWebRequestSubsetInterface $request)
    {
        $this->request = $request;
    }

    /** @return string */
    public function getProtocolVersion()
    {
        if (null === $this->headerReader) {
            $this->loadMessageHeaderReader();
        }

        return $this->headerReader->getProtocolVersion();
    }

    /**
     * @return array|\string[][]
     */
    public function getHeaders()
    {
        if (null === $this->headerReader) {
            $this->loadMessageHeaderReader();
        }

        return $this->headerReader->getHeaders();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        if (null !== $this->headerReader) {
            return $this->headerReader->hasHeader($name);
        }

        return null !== $this->request->getHttpHeader($name);
    }

    /**
     * @param string $name
     *
     * @return array|string[]
     */
    public function getHeader($name)
    {
        if (null === $this->headerReader) {
            $this->loadMessageHeaderReader();
        }

        return $this->headerReader->getHeader($name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        if (null !== $this->headerReader) {
            return $this->headerReader->getHeaderLine($name);
        }

        $value = $this->request->getHttpHeader($name);

        return $value === null? '' : $value;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        if (null === $this->headerReader) {
            $this->loadMessageHeaderReader();
        }

        return $this->headerReader->getMethod();
    }

    /**
     * parses all necessary data from the Symfony request into HeaderReader and resets the initial request
     */
    protected function loadMessageHeaderReader()
    {
        $this->headerReader = RequestAdapter::createReadMinimalRequestHead($this->request);
        unset($this->request);
    }
}
