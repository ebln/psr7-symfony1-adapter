<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use ReflectionObject;

/**
 * TODO
 *      Cookie handling
 *          Access raw Response?
 *          Cookie Abstraction
 *              including Header transcription
 *          how to sync to sfResponse? What happends if header and setRawCookie of sfResponse collide?
 *      withBody and how to sync writes to the stream with the underlying sfWebResponse
 *         via Refl.eventDispatcher && response.filter_content ?
 *         vs. clone Stream on withBody and write to sfResponse
 *      Proper Interface?
 *      Wrapper for Setters using sfEvent ~Dispatcher ?
 */
class Response
{
    use CommonAdapterTrait;
    public const  OPTION_SEND_BODY_ON_204 = 'Will disable automatic setHeaderOnly() if 204 is set as status code.';
    private const STATUS_NO_CONTENT       = 204;

    /** @var array<int,string> */
    protected static $defaultReasonPhrases = [
        308 => 'Permanent Redirect', // defined in RFC-7538
    ];

    /** @var \sfWebResponse */
    protected $sfWebResponse;

    /** @var null|\ReflectionProperty */
    protected $reflexOptions;

    /** @var null|\ReflectionProperty */
    protected $reflexHeaders;

    /** @var bool if setHeaderOnly()-automagic is used on withStatus() calls */
    protected $setHeaderOnly = true;

    private function __construct()
    {
    }

    /**
     * @param \sfWebResponse      $sfWebResponse
     * @param array<string, bool> $options
     *
     * @return Response
     */
    public static function fromSfWebReponse(\sfWebResponse $sfWebResponse, array $options = []): self
    {
        $new                = new static();
        $new->sfWebResponse = $sfWebResponse;

        if (isset($options[self::OPTION_SEND_BODY_ON_204])) {
            $new->setHeaderOnly = false;
        }

        return $new;
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
            $tryKey                       = strtolower($key);
            $headerName                   = $this->headerNames[$tryKey] ?? $key;
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
        return $this->sfWebResponse->getHttpHeader($name) ?? '';
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
        if ($this->setHeaderOnly) {
            $setNoContent = self::STATUS_NO_CONTENT === $code;
            // only change if there's a transition from or to 204
            if ($setNoContent xor self::STATUS_NO_CONTENT === (int)$this->sfWebResponse->getStatusCode()) {
                // only change if HeaderOnly was not overridden externally (using sfWebResponse Object)
                if ($setNoContent xor $this->sfWebResponse->isHeaderOnly()) {
                    $this->sfWebResponse->setHeaderOnly($setNoContent);
                }
            }
        }

        $defaultReasonPhrase = $this->useDefaultReasonPhrase($code, $reasonPhrase);
        $this->sfWebResponse->setStatusCode($code, $defaultReasonPhrase);

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
     * @param array<string, string> $options
     *
     * @throws \ReflectionException
     */
    protected function retroduceOptions(array $options): void
    {
        if (null === $this->reflexOptions) {
            $reflexiveWebResponse = new ReflectionObject($this->sfWebResponse);
            $this->reflexOptions  = $reflexiveWebResponse->getProperty('options');
            $this->reflexOptions->setAccessible(true);
        }

        $this->reflexOptions->setValue($this->sfWebResponse, $options);
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
        if (null === $this->reflexHeaders) {
            $reflexiveWebResponse = new ReflectionObject($this->sfWebResponse);
            $this->reflexHeaders  = $reflexiveWebResponse->getProperty('headers');
            $this->reflexHeaders->setAccessible(true);
        }
        $this->reflexHeaders->setValue($this->sfWebResponse, $headers);
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

        // either return internal default for null to trigger symfony's default lookup
        return static::$defaultReasonPhrases[$code] ?? null;
    }
}
