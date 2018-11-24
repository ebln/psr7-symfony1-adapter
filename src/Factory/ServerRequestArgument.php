<?php

namespace brnc\Symfony1\Message\Factory;

use brnc\Symfony1\Message\Implementation\ReadCommonHead;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface;

/**
 * common DTO for constructing different ServerRequest subset implementation
 */
class ServerRequestArgument
{
    CONST ATTRIBUTE_GET_OPTIONS                  = 'options';
    CONST ATTRIBUTE_GET_REQUEST_PARAMETERS       = 'request';
    CONST ATTRIBUTE_GET_ATTRIBUTE_HOLDER_GET_ALL = 'attributes';
    CONST ATTRIBUTE_GET_PARAMETER_GET_ALL        = 'parameters';
    CONST ATTRIBUTE_SF_WEB_REQUEST               = 'sfWebRequest';
    CONST POPULATE_GET_PARAMETERS                = 'GET';
    CONST POPULATE_POST_PARAMETERS               = 'POST';
    CONST POPULATE_COOKIE_FROM_SUPER_GLOBAL      = 'COOKIE Super Global';

    /** @var string */
    private $method;

    /** @var string */
    private $protocolVersion;

    /** @var array */
    private $headers;

    /** @var array */
    private $serverParams;

    /** @var array */
    private $cookieParams;

    /** @var array */
    private $queryParams;

    /** @var array|null|object */
    private $parsedBody;

    /** @var array|null */
    private $attributes;

    /**
     * @param string            $method
     * @param string            $protocolVersion
     * @param array             $headers
     * @param array             $serverParams
     * @param array             $cookieParams
     * @param array             $queryParams
     * @param null|array|object $parsedBody
     * @param array|null        $attributes
     */
    public function __construct($method, $protocolVersion, array $headers, array $serverParams, array $cookieParams, array $queryParams = null, $parsedBody = null, $attributes = null)
    {
        assert(is_string($protocolVersion));
        $this->protocolVersion = $protocolVersion;
        assert(is_string($method));
        $this->method       = $method;
        $this->headers      = $headers;
        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->queryParams  = $queryParams;
        $this->parsedBody   = $parsedBody;
        $this->attributes   = $attributes;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @return array|null|object
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @return array|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param SfWebRequestSubsetInterface $sfWebRequest
     * @param bool[]                      $options map of ATTRIBUTE_* and POPULATE_* constants to true or false
     *
     * @return ServerRequestArgument
     */
    public static function createFromWebRequest(SfWebRequestSubsetInterface $sfWebRequest, array $options = [])
    {
        $pathInfoArray = $sfWebRequest->getPathInfoArray();
        // gather headers to fit structure of HeaderReader
        $headers = [];
        foreach ($pathInfoArray as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('_', '-', strtolower(substr($key, 5)));

                $headers[strtolower($headerName)] = [
                    ReadCommonHead::HEADER_NAME    => $headerName,
                    ReadCommonHead::HEADER_CONTENT => array_map(function($v) {
                        return trim($v, " \t");
                    }, explode(',', $value)),
                ];
            }
        }

        $protocolVersion = isset($pathInfoArray['SERVER_PROTOCOL'])
                           && preg_match('/^HTTP\/(\d\.\d)$/si', $pathInfoArray['SERVER_PROTOCOL'], $versionMatch)? $versionMatch[1] : '';

        $attributes = [];
        if (isset($options[self::ATTRIBUTE_GET_OPTIONS])) {
            $attributes[self::ATTRIBUTE_GET_OPTIONS] = $sfWebRequest->getOptions();
        }
        if (isset($options[self::ATTRIBUTE_GET_REQUEST_PARAMETERS])) {
            $attributes[self::ATTRIBUTE_GET_REQUEST_PARAMETERS] = $sfWebRequest->getRequestParameters();
        }
        if (isset($options[self::ATTRIBUTE_GET_ATTRIBUTE_HOLDER_GET_ALL])) {
            $attributes[self::ATTRIBUTE_GET_ATTRIBUTE_HOLDER_GET_ALL] = $sfWebRequest->getAttributeHolder()->getAll();
        }
        if (isset($options[self::ATTRIBUTE_GET_REQUEST_PARAMETERS])) {
            $attributes[self::ATTRIBUTE_GET_REQUEST_PARAMETERS] = $sfWebRequest->getParameterHolder()->getAll();
        }
        if (isset($options[self::ATTRIBUTE_SF_WEB_REQUEST])) {
            $attributes[self::ATTRIBUTE_SF_WEB_REQUEST] = $sfWebRequest;
        }

        $request = new static($sfWebRequest->getMethod(), $protocolVersion, $headers, $pathInfoArray, isset($options[self::POPULATE_COOKIE_FROM_SUPER_GLOBAL])? $_COOKIE : [], isset($options[self::POPULATE_GET_PARAMETERS])? $sfWebRequest->getGetParameters() : [], isset($options[self::POPULATE_POST_PARAMETERS])? $sfWebRequest->getPostParameters() : null, empty($attributes)? null : $attributes);

        return $request;
    }
}
