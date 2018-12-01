<?php

namespace brnc\Symfony1\Message\Adapter;

use brnc\Contract\Http\Message\CommonHeadInterface;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface;

/**
 * TODO
 */
class Request implements CommonHeadInterface // TODO implements ServerRequestInterface
{
    use CommonAdapterTrait;
    CONST ATTRIBUTE_SF_WEB_REQUEST = 'sfWebRequest';

    /** @var bool[] */
    protected static $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];

    /** @var \sfWebRequest|SfWebRequestSubsetInterface */
    protected $sfWebRequest;

    /** @var \ReflectionProperty */
    protected $reflexivePropertyPathInfoArray;

    /** @var mixed[] */
    protected $attributes = [];

    /**
     * @var string
     *
     * shadow to honour: »[…]method names are case-sensitive and thus implementations SHOULD NOT modify the given
     * string.«
     */
    protected $method;

    /**
     * @param SfWebRequestSubsetInterface $sfWebRequest
     * @param bool                        $populateAttributes
     */
    public function __construct(SfWebRequestSubsetInterface $sfWebRequest, $populateAttributes = false)
    {
        $this->sfWebRequest = $sfWebRequest;
        // inititialise path array
        $sfWebRequest->getPathInfoArray();

        if ($populateAttributes) {
            $attributes[self::ATTRIBUTE_SF_WEB_REQUEST] = $sfWebRequest;
        }
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->getVersionFromArray($this->sfWebRequest->getPathInfoArray(), 'SERVER_PROTOCOL');
    }

    /**
     * @param string $version
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withProtocolVersion($version)
    {
        $pathInfoArray                    = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray['SERVER_PROTOCOL'] = 'HTTP/' . $version;
        $this->setPathInfoArray($pathInfoArray);

        return $this;
    }

    /**
     * @return string[][]
     */
    public function getHeaders()
    {
        $headers = [];
        foreach ($this->sfWebRequest->getPathInfoArray() as $key => $value) {
            $useKey = null;
            if (strpos($key, 'HTTP_') === 0) {
                $useKey = substr($key, 5);
            }
            elseif (isset(self::$contentHeaders[$key])) {
                $useKey = $key;
            }

            if (null !== $useKey) {
                $headerName = $this->normalizeHeaderName($useKey);

                if (isset($this->headerNames[$headerName])) {
                    $headerName = $this->headerNames[$headerName];
                }

                $headers[$headerName] = $this->explodeHeaderLine($value);
            }
        }

        return $headers;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        $value = $this->sfWebRequest->getHttpHeader($name);

        return $value === null? '' : $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return null !== $this->sfWebRequest->getHttpHeader($name);
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name)
    {
        $value = $this->sfWebRequest->getHttpHeader($name);

        return $value === null? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withoutHeader($name)
    {
        $keyName       = $this->getPathInfoKey($name);
        $pathInfoArray = $this->sfWebRequest->getPathInfoArray();
        unset($pathInfoArray[$keyName]);
        $this->setPathInfoArray($pathInfoArray);
        unset($this->headerNames[$this->normalizeHeaderName($name)]);

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        $method = $this->sfWebRequest->getMethod();
        if ($this->method && $method === strtoupper($this->method)) {
            return $this->method;
        }
        $this->method = null;

        return $method;
    }

    /**
     * @param string $method
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withMethod($method)
    {
        $this->method = $method;
        $this->sfWebRequest->setMethod($method);

        return $this;
    }

    /**
     * wrapper for symfony's getPathInfoArray()
     *
     * @return array symfony's getPathInfoArray()
     */
    public function getServerParams()
    {
        return $this->sfWebRequest->getPathInfoArray();
    }

    /**
     * TODO: check SG-header-congruency
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $_COOKIE; // as getCookie() is nothing but a lookup
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->sfWebRequest->getGetParameters();
    }

    /**
     * @return array
     */
    public function getParsedBody()
    {
        return $this->sfWebRequest->getPostParameters();
    }

    /**
     * @return mixed[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes)? $this->attributes[$name] : $default;
    }

    /**
     * @param string $name
     *
     * @param  mixed $value
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method doesn't return a clone but the instance
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method doesn't return a clone but the instance
     */
    public function withoutAttribute($name)
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * sets symfony request's pathInfoArray property using reflection
     *
     * @param array $pathInfo
     */
    protected function setPathInfoArray(array $pathInfo)
    {
        if (null === $this->reflexivePropertyPathInfoArray) {
            $reflexiveWebRequest                  = new \ReflectionObject($this->sfWebRequest);
            $this->reflexivePropertyPathInfoArray = $reflexiveWebRequest->getProperty('pathInfoArray');
            $this->reflexivePropertyPathInfoArray->setAccessible(true);
        }

        $this->reflexivePropertyPathInfoArray->setValue($this->sfWebRequest, $pathInfo);
    }

    /**
     * injects a header into symfony's pathInfoArray via setPathInfoArray()'s reflection
     *
     * @param string          $name
     * @param string|string[] $value
     */
    protected function setHeader($name, $value)
    {
        $keyName                 = $this->getPathInfoKey($name);
        $pathInfoArray           = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray[$keyName] = $this->implodeHeaders($value);
        $this->setPathInfoArray($pathInfoArray);
    }

    /**
     * get the array key resp. to pathInfoArray from the header field name
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPathInfoKey($name)
    {
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (!isset(self::$contentHeaders[$name])) {
            $keyName = 'HTTP_' . $name;
        }

        return $keyName;
    }
}
