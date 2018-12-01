<?php

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface;
use Psr\Http\Message\UriInterface;

/**
 * TODO
 */
class Request // TODO implements ServerRequestInterface
{
    /** @var bool[] */
    protected static $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];

    /** @var \sfWebRequest|SfWebRequestSubsetInterface */
    protected $sfWebRequest;

    /** @var \ReflectionProperty */
    protected $reflexivePropertyPathInfoArray;

    /**
     * @var string[]
     *
     * shadow to honour: »[…]preserve the exact case in which headers were originally specified.«
     */
    protected $headerNames = [];

    /**
     * @var string
     *
     * shadow to honour: »[…]method names are case-sensitive and thus implementations SHOULD NOT modify the given
     * string.«
     */
    protected $method;

    /**
     * @param SfWebRequestSubsetInterface $sfWebRequest
     */
    public function __construct(SfWebRequestSubsetInterface $sfWebRequest)
    {
        $this->sfWebRequest = $sfWebRequest;
        // init path array
        $sfWebRequest->getPathInfoArray();

        $reflexiveWebRequest                  = new \ReflectionObject($this->sfWebRequest);
        $this->reflexivePropertyPathInfoArray = $reflexiveWebRequest->getProperty('pathInfoArray');
        $this->reflexivePropertyPathInfoArray->setAccessible(true);
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        $pathInfoArray   = $this->sfWebRequest->getPathInfoArray();
        $protocolVersion = (isset($pathInfoArray['SERVER_PROTOCOL'])
                            && preg_match('/^HTTP\/(\d\.\d)$/si', $pathInfoArray['SERVER_PROTOCOL'],
                                          $versionMatch))? $versionMatch[1] : '';

        return $protocolVersion;
    }

    /**
     * @param string $version
     *
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $pathInfoArray                    = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray['SERVER_PROTOCOL'] = 'HTTP/' . $version;
        $this->setPathInfoArray($pathInfoArray);

        return $this;
    }

    /**
     * @param array $pathInfo
     */
    protected function setPathInfoArray(array $pathInfo)
    {
        $this->reflexivePropertyPathInfoArray->setValue($this->sfWebRequest, $pathInfo);
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
    protected function normalizeHeaderName($name)
    {
        return str_replace('_', '-', strtolower($name));
    }

    /**
     * @param string $line
     *
     * @return string[]
     */
    protected function explodeHeaderLine($line)
    {
        return array_map(function($v) {
            return trim($v, " \t"); // https://tools.ietf.org/html/rfc7230#section-3.2.4
        }, explode(',', $line));
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
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        if (!$this->hasHeader($name)) {
            // to preserve the original header name
            return $this->withHeader($name, $value);
        }

        $headers = $this->getHeader($name);
        if (is_array($value)) {
            $headers = array_merge($headers, $value);
        }
        else {
            $headers[] = $headers;
        }

        $this->setHeader($name, $headers);

        return $this;
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
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        $this->headerNames[$this->normalizeHeaderName($name)] = $name;

        $this->setHeader($name, $value);

        return $this;
    }

    /**
     * injects a header into symfony's pathInfoArray via setPathInfoArray()'s reflection
     *
     * @param string          $name
     * @param string|string[] $value
     */
    protected function setHeader($name, $value)
    {
        $keyName = $this->getPathInfoKey($name);

        $pathInfoArray           = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray[$keyName] = is_array($value)? implode(',', $value) : $value;
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
     * @return static
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
     *
     */
    public function getRequestTarget()
    {
    }

    /**
     * @param $requestTarget
     */
    public function withRequestTarget($requestTarget)
    {
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
     * @return static
     */
    public function withMethod($method)
    {
        $this->method = $method;
        $this->sfWebRequest->setMethod($method);

        return $this;
    }

    /**
     * TODO!
     */
    public function getUri()
    {
    }

    /**
     * @param UriInterface $uri
     * @param bool         $preserveHost
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
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
     * @param array $cookies
     */
    public function withCookieParams(array $cookies)
    {
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->sfWebRequest->getGetParameters();
    }

    /**
     * @param array $query
     */
    public function withQueryParams(array $query)
    {
    }
}
