<?php

namespace brnc\Symfony1\Message\Obligation;

/**
 * Transparent proxy to wrap \sfWebRequest just once in order to
 * completely rely on the sfWebRequestSubsetInterface afterwards esp. by means of type hinting
 */
class sfWebRequestSubsetProxy implements sfWebRequestSubsetInterface
{
    /** @var sfWebRequestSubsetInterface|\sfWebRequest */
    private $request;

    /**
     * @param sfWebRequestSubsetInterface|\sfWebRequest $request
     *
     * @return sfWebRequestSubsetInterface
     * @throws \InvalidArgumentException
     */
    public static function create($request)
    {
        if ((class_exists('\sfWebRequest') && $request instanceof \sfWebRequest)
            || ($request instanceof sfWebRequestSubsetInterface)) {
            return new static($request);
        }

        // TODO Error class!
        throw new \InvalidArgumentException('Expected Symfony 1 sfWebRequest class!');
    }

    /**
     * @param sfWebRequestSubsetInterface|\sfWebRequest $request
     */
    private function __construct($request)
    {
        $this->request = $request;
    }

    /** @inheritdoc */
    public function getMethod()
    {
        return $this->request->getMethod();
    }

    /** @inheritdoc */
    public function getPathInfoArray()
    {
        return $this->request->getPathInfoArray();
    }

    /** @inheritdoc */
    public function getHttpHeader($name)
    {
        return $this->request->getHttpHeader($name);
    }

    /** @inheritdoc */
    public function getOptions()
    {
        return $this->request->getOptions();
    }

    /** @inheritdoc */
    public function getRequestParameters()
    {
        return $this->request->getRequestParameters();
    }

    /** @inheritdoc */
    public function getAttributeHolder()
    {
        return $this->request->getAttributeHolder();
    }

    /** @inheritdoc */
    public function getParameterHolder()
    {
        return $this->request->getParameterHolder();
    }

    /** @inheritdoc */
    public function getGetParameters()
    {
        return $this->request->getGetParameters();
    }

    /** @inheritdoc */
    public function getPostParameters()
    {
        return $this->request->getPostParameters();
    }

    /** @inheritdoc */
    public function getCookie($name, $defaultValue = null)
    {
        return $this->request->getCookie($name, $defaultValue = null);
    }
}
