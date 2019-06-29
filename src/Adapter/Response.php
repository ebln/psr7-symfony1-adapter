<?php

namespace brnc\Symfony1\Message\Adapter;

use ReflectionObject;

/**
 * TODO
 *      Cookie handling
 *          Access raw Response?
 *          Cookie Abstraction
 *              including Header transcription
 *      Proper Interface?
 */
class Response
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
    public function getProtocolVersion(): string
    {
        return $this->getVersionFromArray($this->sfWebResponse->getOptions(), 'http_protocol');
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
        $options                  = $this->sfWebResponse->getOptions();
        $options['http_protocol'] = 'HTTP/' . $version;
        $this->retroduceOptions($options);

        return $this;
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
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
    public function getHeaderLine($name): string
    {
        return $this->sfWebResponse->getHttpHeader($name, '');
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return $this->sfWebResponse->hasHttpHeader($name);
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name): array
    {
        /** @noinspection ArgumentEqualsDefaultValueInspection */
        $value = $this->sfWebResponse->getHttpHeader($name, null);

        return $value === null ? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withoutHeader($name): self
    {
        unset($this->headerNames[$this->normalizeHeaderName($name)]);
        /** @noinspection ArgumentEqualsDefaultValueInspection */
        $this->sfWebResponse->setHttpHeader($name, null, true);

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
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
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $defaultedReasonPhrase = $this->useDefaultReasonPhrase($code, $reasonPhrase);
        $this->sfWebResponse->setStatusCode($code, $defaultedReasonPhrase);

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->sfWebResponse->getStatusText();
    }

    /**
     * sets symfony response's options property using reflection
     *
     * @param array $options
     *
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    protected function retroduceOptions(array $options): void
    {
        if (null === $this->reflexivePropertyOptions) {
            $reflexiveWebResponse           = new ReflectionObject($this->sfWebResponse);
            $this->reflexivePropertyOptions = $reflexiveWebResponse->getProperty('options');
            $this->reflexivePropertyOptions->setAccessible(true);
        }

        $this->reflexivePropertyOptions->setValue($this->sfWebResponse, $options);
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @throws \ReflectionException
     */
    protected function setHeader($name, $value): void
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
    protected function normalizeSymfonyHeaderName($name): string
    {
        return strtr(ucwords(strtr(strtolower($name), ['_' => ' ', '-' => ' '])), [' ' => '-']);
    }

    /**
     * sets symfony response's headers property using reflection
     *
     * @param string[] $headers
     *
     * @throws \ReflectionException
     */
    protected function retroduceHeaders(array $headers): void
    {
        if (null === $this->reflexivePropertyHeaders) {
            $reflexiveWebResponse           = new ReflectionObject($this->sfWebResponse);
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
    protected function useDefaultReasonPhrase(int $code, string $reasonPhrase): ?string
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
