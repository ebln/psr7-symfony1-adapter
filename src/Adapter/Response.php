<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Utillity\Assert;
use ReflectionObject;

/**
 * TODO
 *      Cookie handling
 *          Access raw Response?
 *          Cookie Abstraction
 *              including Header transcription
 *          how to sync to sfResponse? What happens if header and setRawCookie of sfResponse collide?
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
    private static $defaultReasonPhrases = [
        308 => 'Permanent Redirect', // defined in RFC-7538
    ];

    /** @var \sfWebResponse */
    private $sfWebResponse;

    /** @var null|\ReflectionProperty */
    private $reflexOptions;

    /** @var null|\ReflectionProperty */
    private $reflexHeaders;

    /** @var bool if setHeaderOnly() auto-magic is used on withStatus() calls */
    private $setHeaderOnly = true;

    private function __construct()
    {
    }

    /**
     * @param array<string, bool> $options
     *
     * @return Response
     */
    public static function fromSfWebResponse(\sfWebResponse $sfWebResponse, array $options = []): self
    {
        $new                = new static();
        $new->sfWebResponse = $sfWebResponse;

        if (isset($options[self::OPTION_SEND_BODY_ON_204])) {
            $new->setHeaderOnly = false;
        }

        return $new;
    }

    public function getProtocolVersion(): string
    {
        return $this->getVersionFromArray($this->sfWebResponse->getOptions(), 'http_protocol');
    }

    /**
     * @param string $version
     *
     * @throws \ReflectionException
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
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
     */
    public function getHeaderLine($name): string
    {
        return $this->sfWebResponse->getHttpHeader($name) ?? '';
    }

    /**
     * @param string $name
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

        return null === $value ? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withoutHeader($name): self
    {
        Assert::stringNotEmpty($name);
        unset($this->headerNames[$this->normalizeHeaderName($name)]);
        /* @noinspection ArgumentEqualsDefaultValueInspection */
        $this->sfWebResponse->setHttpHeader($name, null, true);

        return $this;
    }

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
    private function retroduceOptions(array $options): void
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
    private function setHeader($name, $value): void
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
     */
    private function normalizeSymfonyHeaderName($name): string
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
    private function retroduceHeaders(array $headers): void
    {
        if (null === $this->reflexHeaders) {
            $reflexiveWebResponse = new ReflectionObject($this->sfWebResponse);
            $this->reflexHeaders  = $reflexiveWebResponse->getProperty('headers');
            $this->reflexHeaders->setAccessible(true);
        }
        $this->reflexHeaders->setValue($this->sfWebResponse, $headers);
    }

    private function useDefaultReasonPhrase(int $code, string $reasonPhrase): ?string
    {
        if (!empty($reasonPhrase)) {
            return $reasonPhrase;
        }

        // either return internal default for null to trigger symfony's default lookup
        return static::$defaultReasonPhrases[$code] ?? null;
    }

    /**
     * @return static
     */
    private function getThisOrClone(): self
    {
        return $this; // TODO implement
    }
}
