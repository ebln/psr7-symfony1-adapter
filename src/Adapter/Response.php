<?php

namespace brnc\Symfony1\Message\Adapter;

use brnc\Contract\Http\Message\CommonHeadInterface;

/**
 * TODO
 *      Proper Interface
 *      Cookie handling
 *          Access raw Response?
 *          Cookie Abstraction
 *              including Header transcription
 *      ResponseProxy
 *      Make this abstract and use falsification traits to fake PSR-7 compliance
 *          as well as to suggest extentions, to contruct from sfWebRequest
 */
class Response implements CommonHeadInterface
{
    use CommonAdapterTrait;

    /** @var string[] */
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
     * @param \sfWebResponse $sfWebResponse
     */
    public function __construct(\sfWebResponse $sfWebResponse)
    {
        $this->sfWebResponse = $sfWebResponse;
    }

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->getVersionFromArray($this->sfWebResponse->getOptions(), 'http_protocol');
    }

    /**
     * @param string $version
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withProtocolVersion($version)
    {
        $options                  = $this->sfWebResponse->getOptions();
        $options['http_protocol'] = 'HTTP/' . $version;
        $this->retroduceOptions($options);

        return $this;
    }

    /**
     * @return string[][]
     */
    public function getHeaders()
    {
        $shadowedHeaders = [];

        foreach ($this->sfWebResponse->getHttpHeaders() as $key => $value) {
            $tryKey = strtolower($key);
            if (isset($this->headerNames[$tryKey])) {
                $headerName = $this->headerNames[$tryKey];
            } else {
                $headerName = $key;
            }

            $shadowedHeaders[$headerName] = $this->explodeHeaderLine($value);
        }

        return $shadowedHeaders;
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
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return $this->sfWebResponse->hasHttpHeader($name);
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name)
    {
        $value = $this->sfWebResponse->getHttpHeader($name, null);

        return $value === null ? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
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
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $defaultedReasonPhrase = $this->useDefaultReasonPhrase($code, $reasonPhrase);
        $this->sfWebResponse->setStatusCode($code, $defaultedReasonPhrase);

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->sfWebResponse->getStatusText();
    }

    /**
     * sets symfony response's options property using reflection
     *
     * @param array $options
     */
    protected function retroduceOptions(array $options)
    {
        if (null === $this->reflexivePropertyOptions) {
            $reflexiveWebResponse           = new \ReflectionObject($this->sfWebResponse);
            $this->reflexivePropertyOptions = $reflexiveWebResponse->getProperty('options');
            $this->reflexivePropertyOptions->setAccessible(true);
        }

        $this->reflexivePropertyOptions->setValue($this->sfWebResponse, $options);
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     */
    protected function setHeader($name, $value)
    {
        $symfonyKey           = $this->normalizeSymfonyHeaderName($name);
        $headers              = $this->sfWebResponse->getHttpHeaders();
        $headers[$symfonyKey] = $this->implodeHeaders($value);
        $this->retroduceHeaders($headers);
    }

    /**
     * get the symfony's header name used by sfWebResponse's headers property
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeSymfonyHeaderName($name)
    {
        return strtr(ucwords(strtr(strtolower($name), ['_' => ' ', '-' => ' '])), [' ' => '-']);
    }

    /**
     * sets symfony response's headers property using reflection
     *
     * @param string[] $headers
     */
    protected function retroduceHeaders(array $headers)
    {
        if (null === $this->reflexivePropertyHeaders) {
            $reflexiveWebResponse           = new \ReflectionObject($this->sfWebResponse);
            $this->reflexivePropertyHeaders = $reflexiveWebResponse->getProperty('headers');
            $this->reflexivePropertyHeaders->setAccessible(true);
        }
        $this->reflexivePropertyHeaders->setValue($this->sfWebResponse, $headers);
    }

    /**
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return string|null
     */
    protected function useDefaultReasonPhrase($code, $reasonPhrase)
    {
        if (!empty($reasonPhrase)) {
            return $reasonPhrase;
        }
        // to trigger symfony's default lookup
        $defaultedReasonPhrase = null;
        // override for 308
        if (isset(static::$defaultReasonPhrases[$code])) {
            $defaultedReasonPhrase = static::$defaultReasonPhrases[$code];
        }

        return $defaultedReasonPhrase;
    }
}
