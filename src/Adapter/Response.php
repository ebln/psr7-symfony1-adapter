<?php

// TODO – common return $this including warning; access raw-request, access, cookies?
namespace brnc\Symfony1\Message\Adapter;

class Response // TODO implements ResponseInterface
{
    /**
     * @var string[]
     */
    protected static $defaultReasonPhrases = [
        308 => 'Permanent Redirect', // defined in RFC-7538
    ];

    /** @var \sfWebResponse */
    protected $sfWebResponse;

    /** @var \ReflectionProperty */
    protected $reflexivePropertyOptions;

    /** @var \ReflectionProperty */
    protected $reflexivePropertyHeaders;

    /**
     * @var string[]
     *
     * shadow to honour: »[…]preserve the exact case in which headers were originally specified.«
     */
    protected $headerNames = [];

    /**
     * TODO ad minimal interface!
     *
     * @param \sfWebResponse $sfWebResponse
     */
    public function __construct(\sfWebResponse $sfWebResponse)
    {
        $this->sfWebResponse = $sfWebResponse;

        $reflexiveWebResponse           = new \ReflectionObject($this->sfWebResponse);
        $this->reflexivePropertyOptions = $reflexiveWebResponse->getProperty('options');
        $this->reflexivePropertyOptions->setAccessible(true);

        $this->reflexivePropertyHeaders = $reflexiveWebResponse->getProperty('headers');
        $this->reflexivePropertyHeaders->setAccessible(true);
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        $options         = $this->sfWebResponse->getOptions();
        $protocolVersion = (isset($options['http_protocol'])
                            && preg_match('/^HTTP\/(\d\.\d)$/si', $options['http_protocol'],
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
        $options                  = $this->sfWebResponse->getOptions();
        $options['http_protocol'] = 'HTTP/' . $version;
        $this->setOptions($options);

        return $this;
    }

    /**
     * @param array $options
     */
    protected function setOptions(array $options)
    {
        $this->reflexivePropertyOptions->setValue($this->sfWebResponse, $options);
    }

    /**
     * @return string[][]
     */
    public function getHeaders()
    {
        $shadowedHeaders = [];

        foreach ($this->getSymfonyHeaders() as $key => $value) {
            $tryKey = strtolower($key);
            if (isset($this->headerNames[$tryKey])) {
                $headerName = $this->headerNames[$tryKey];
            }
            else {
                $headerName = $key;
            }

            $shadowedHeaders[$headerName] = $this->explodeHeaderLine($value);
        }

        return $shadowedHeaders;
    }

    /**
     * only fixes the IDE hinting, due to bogus phpdoc of getHttpHeaders()
     *
     * @return string[]
     */
    protected function getSymfonyHeaders()
    {
        /** @var string[] $headers */
        $headers = $this->sfWebResponse->getHttpHeaders();

        return $headers;
    }

    /**
     * @param string $line
     *
     * @return string[]
     */
    protected function explodeHeaderLine($line)
    {
        return array_map(function($v) {
            return trim($v, " \t");
        }, explode(',', $line));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        return $this->sfWebResponse->getHttpHeader($name, '');
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
        return $this->sfWebResponse->hasHttpHeader($name);
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        // TODO had header name validation! (would be only valid for shadow)
        $this->headerNames[$this->normalizeHeaderName($name)] = $name;
        $this->setHeader($name, $value); // raw access
        // $this->sfWebResponse->setHttpHeader($name, $value, true); // for using the fixContentType() extra mile

        return $this;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function normalizeHeaderName($name)
    {
        // let's test.. if [] → '' works…
        return str_replace(['_', ' '], '-', strtolower($name));
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     */
    protected function setHeader($name, $value)
    {
        $symfonyKey           = $this->normalizeSymfonyHeaderName($name);
        $headers              = $this->getSymfonyHeaders();
        $headers[$symfonyKey] = is_array($value)? implode(',', $value) : $value;
        $this->setHeaders($headers); // raw access
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function normalizeSymfonyHeaderName($name)
    {
        return strtr(ucwords(strtr(strtolower($name), ['_' => ' ', '-' => ' '])), [' ' => '-']);
    }

    /**
     * @param string[] $headers
     */
    protected function setHeaders(array $headers)
    {
        $this->reflexivePropertyHeaders->setValue($this->sfWebResponse, $headers);
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name)
    {
        $value = $this->sfWebResponse->getHttpHeader($name, null);

        return $value === null? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function withoutHeader($name)
    {
        unset($this->headerNames[$this->normalizeHeaderName($name)]);
        $this->sfWebResponse->setHttpHeader($name, null, true);

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->sfWebResponse->getStatusCode();
    }

    /**
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return static
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $reasonPhrase = $this->useDefaultReasonPhrase($code, $reasonPhrase);
        $this->sfWebResponse->setStatusCode($code, $reasonPhrase);

        return $this;
    }

    /**
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return string|null
     */
    protected function useDefaultReasonPhrase($code, $reasonPhrase)
    {
        if (empty($reasonPhrase)) {
            // to trigger symfony's default lookup
            $reasonPhrase = null;
            // override for 308
            if (isset(static::$defaultReasonPhrases[$code])) {
                $reasonPhrase = static::$defaultReasonPhrases[$code];
            }
        }

        return $reasonPhrase;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->sfWebResponse->getStatusText();
    }
}
