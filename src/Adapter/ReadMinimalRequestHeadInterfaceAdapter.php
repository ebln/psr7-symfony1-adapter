<?php

namespace brnc\Symfony1\Message\Adapter;

use brnc\Contract\Http\Message\MinimalRequestHeaderReadInterface;
use brnc\Symfony1\Message\Factory\RequestAdapter;
use brnc\Symfony1\Message\Obligation\sfWebRequestSubsetInterface;
use brnc\Symfony1\Message\ReadMinimalRequestHead;

/**
 * Limited subject read-only Adapter/Proxy for sfWebRequest objects
 */
class ReadMinimalRequestHeadInterfaceAdapter implements MinimalRequestHeaderReadInterface
{
    /** @var sfWebRequestSubsetInterface */
    protected $request;

    /** @var ReadMinimalRequestHead */
    protected $headerReader;

    /**
     * @param sfWebRequestSubsetInterface $request
     */
    public function __construct(sfWebRequestSubsetInterface $request)
    {
        $this->request = $request;
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
        $this->headerReader = RequestAdapter::createRequestHeaderReader($this->request);
        unset($this->request);
    }
}
