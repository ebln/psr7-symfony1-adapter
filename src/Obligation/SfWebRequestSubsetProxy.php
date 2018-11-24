<?php /** @noinspection PhpUndefinedClassInspection */

namespace brnc\Symfony1\Message\Obligation;

/**
 * Transparent proxy to wrap \sfWebRequest just once in order to
 * completely rely on the sfWebRequestSubsetInterface afterwards esp. by means of type hinting
 */
class SfWebRequestSubsetProxy implements SfWebRequestSubsetInterface
{
    /** @var SfWebRequestSubsetInterface|\sfWebRequest */
    private $request;

    /**
     * @param SfWebRequestSubsetInterface|\sfWebRequest $request
     *
     * @return SfWebRequestSubsetInterface
     * @throws NoSfWebRequestException
     */
    public static function create($request)
    {
        if ((class_exists('\sfWebRequest') && $request instanceof \sfWebRequest)
            || ($request instanceof SfWebRequestSubsetInterface)) {
            return new static($request);
        }

        $errorMessage = NoSfWebRequestException::DEFAULT_MSG . ' '
                        . (is_object($request)? get_class($request) : gettype($request)) . ' provided.';

        throw new NoSfWebRequestException($errorMessage);
    }

    /**
     * @param SfWebRequestSubsetInterface|\sfWebRequest $request
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
        return $this->request->getCookie($name, $defaultValue);
    }
}
