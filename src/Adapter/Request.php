<?php

namespace brnc\Symfony1\Message\Adapter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request implements ServerRequestInterface
{
    /** @var bool[] */
    protected static $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];

    /** @var \sfWebRequest */
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
     * @param \sfWebRequest $sfWebRequest
     */
    public function __construct(\sfWebRequest $sfWebRequest)
    {
        $this->sfWebRequest = $sfWebRequest;
        // init path array
        $sfWebRequest->getPathInfoArray();

        $reflexiveWebRequest                  = new \ReflectionObject($this->sfWebRequest);
        $this->reflexivePropertyPathInfoArray = $reflexiveWebRequest->getProperty('pathInfoArray');
        $this->reflexivePropertyPathInfoArray->setAccessible(true);
    }

    /**
     * @param array $pathInfo
     */
    private function setPathInfoArray(array $pathInfo)
    {
        $this->reflexivePropertyPathInfoArray->setValue($this->sfWebRequest, $pathInfo);
    }

    /**
     * @inheritdoc.
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
    private function normalizeHeaderName($name)
    {
        return str_replace('_', '-', strtolower($name));
    }

    /**
     * @param string $line
     *
     * @return string[]
     */
    private function explodeHeaderLine($line)
    {
        return array_map(function($v) {
            return trim($v, " \t");
        }, explode(',', $line));
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
     */
    private function setHeader($name, $value)
    {
        $keyName = $this->getPathInfoKey($name);

        $pathInfoArray           = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray[$keyName] = is_array($value)? implode(',', $value) : $value;
        $this->setPathInfoArray($pathInfoArray);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getPathInfoKey($name)
    {
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (!isset(self::$contentHeaders[$name])) {
            $keyName = 'HTTP_' . $name;
        }

        return $keyName;
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

    public function getBody()
    {
    }

    public function withBody(StreamInterface $body)
    {
    }

    public function getRequestTarget()
    {
    }

    public function withRequestTarget($requestTarget)
    {
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
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
     * TODO:!
     */
    public function getUri()
    {
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
    }

    /**
     * wrapper for
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

    public function withQueryParams(array $query)
    {
    }

    public function getUploadedFiles()
    {
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
    }

    /**
     * @return array
     */
    public function getParsedBody()
    {
        return $this->sfWebRequest->getPostParameters();
    }

    public function withParsedBody($data)
    {
    }

    public function getAttributes()
    {
    }

    public function getAttribute($name, $default = null)
    {
    }

    public function withAttribute($name, $value)
    {
    }

    public function withoutAttribute($name)
    {
    }
}
