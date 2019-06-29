<?php

namespace brnc\Symfony1\Message\Adapter;

use ReflectionObject;

/**
 * TODO
 *      Cookie handling
 *          Cookie Abstraction
 *              including Header transcription
 *      Request Target and URI handling (using guzzle PSR-7?)
 *      Proper Interface?
 */
class Request
{
    use CommonAdapterTrait;
    public CONST ATTRIBUTE_SF_WEB_REQUEST = 'sfWebRequest';

    /** @var bool[] */
    protected static $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];

    /** @var \sfWebRequest */
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
     * @param \sfWebRequest $sfWebRequest
     * @param bool          $populateAttributes
     */
    public function __construct(\sfWebRequest $sfWebRequest, bool $populateAttributes = false)
    {
        $this->sfWebRequest = $sfWebRequest;
        // inititialise path array
        $sfWebRequest->getPathInfoArray();

        if ($populateAttributes) {
            $this->attributes[self::ATTRIBUTE_SF_WEB_REQUEST] = $sfWebRequest;
        }
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->getVersionFromArray($this->sfWebRequest->getPathInfoArray(), 'SERVER_PROTOCOL');
    }

    /**
     * @param string $version
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     * @throws \ReflectionException
     */
    public function withProtocolVersion($version): self
    {
        $pathInfoArray                    = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray['SERVER_PROTOCOL'] = 'HTTP/' . $version;
        $this->retroducePathInfoArray($pathInfoArray);

        return $this;
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->sfWebRequest->getPathInfoArray() as $key => $value) {
            $useKey = null;
            if (strpos($key, 'HTTP_') === 0) {
                $useKey = substr($key, 5);
            } elseif (isset(self::$contentHeaders[$key])) {
                $useKey = $key;
            }

            if (null !== $useKey) {
                $headerName = $this->normalizeHeaderName($useKey);

                if (isset($this->headerNames[$headerName])) {
                    $headerName = $this->headerNames[$headerName]; // return shadowed header name
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
    public function getHeaderLine($name): string
    {
        $value = $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));

        return $value ?? '';
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return null !== $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name): array
    {
        $value = $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));

        return $value === null ? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     * @throws \ReflectionException
     */
    public function withoutHeader($name): self
    {
        $keyName       = $this->getPathInfoKey($name);
        $pathInfoArray = $this->sfWebRequest->getPathInfoArray();
        unset($pathInfoArray[$keyName]);
        $this->retroducePathInfoArray($pathInfoArray);
        unset($this->headerNames[$this->normalizeHeaderName($name)]);

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): ?string
    {
        $method = $this->sfWebRequest->getMethod();
        if ($this->method && $method === strtoupper($this->method)) {
            return $this->method;   // return shadowed capitalisation
        }
        $this->method = null;       // unset shadowed value

        return $method;             // return value from real object as default
    }

    /**
     * @param string $method
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withMethod($method): self
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
    public function getServerParams(): array
    {
        return $this->sfWebRequest->getPathInfoArray();
    }

    /**
     * TODO: check SG-header-congruency
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        return $_COOKIE; // as getCookie() is nothing but a lookup
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->sfWebRequest->getGetParameters();
    }

    /**
     * @return array
     */
    public function getParsedBody(): array
    {
        return $this->sfWebRequest->getPostParameters();
    }

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
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
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @param string $name
     *
     * @param mixed  $value
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method doesn't return a clone but the instance
     */
    public function withAttribute($name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method doesn't return a clone but the instance
     */
    public function withoutAttribute($name): self
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * sets symfony request's pathInfoArray property using reflection
     *
     * @param array $pathInfo
     *
     * @throws \ReflectionException
     */
    protected function retroducePathInfoArray(array $pathInfo): void
    {
        if (null === $this->reflexivePropertyPathInfoArray) {
            $reflexiveWebRequest                  = new ReflectionObject($this->sfWebRequest);
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
     *
     * @throws \ReflectionException
     */
    protected function setHeader(string $name, $value): void
    {
        $keyName                 = $this->getPathInfoKey($name);
        $pathInfoArray           = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray[$keyName] = $this->implodeHeaders($value);
        $this->retroducePathInfoArray($pathInfoArray);
    }

    /**
     * get the array key resp. to pathInfoArray from the header field name
     *
     * @param string $name
     *
     * @return string
     */
    protected function getPathInfoKey(string $name) : string
    {
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (!isset(self::$contentHeaders[$keyName])) {
            $keyName = 'HTTP_' . $keyName;
        }

        return $keyName;
    }

    /**
     * in order to also obtain content headers via getHttpHeader()
     *
     * @param string $name
     *
     * @return string|null
     */
    protected function getPathInfoPrefix(string $name): ?string
    {
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (isset(self::$contentHeaders[$keyName])) {
            return null;
        }

        return 'HTTP';
    }
}
