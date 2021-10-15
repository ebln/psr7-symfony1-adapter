<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Utillity\Assert;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionObject;

/**
 * TODO
 *      Cookie handling
 *          Access raw Response?
 *          Cookie Abstraction
 *              including Header transcription
 *          how to sync to sfResponse? What happens if header and setRawCookie of sfResponse collide?
 *
 * @psalm-consistent-constructor
 */
class Response implements ResponseInterface
{
    use CommonAdapterTrait;

    public const OPTION_SEND_BODY_ON_204    = 'Will disable automatic setHeaderOnly() if 204 is set as status code.';
    public const OPTION_IMMUTABLE_VIOLATION = 'Return mutated self';   // Violates PSR-7's immutability, as this is an adapter acting on the underlying sfWebRequest
    private const STATUS_NO_CONTENT         = 204;
    private const SFR_HTTP_PROTOCOL_OPTION  = 'http_protocol';
    private const SFR_STREAM_HOOK_OPTION    = '__brncBodyStreamHook';

    /** @var array<int,string> */
    private static array $defaultReasonPhrases = [
        308 => 'Permanent Redirect', // defined in RFC-7538
    ];

    /** @var \sfWebResponse */
    private $sfWebResponse;

    /** @var null|\ReflectionProperty */
    private $reflexOptions;

    /** @var null|\ReflectionProperty */
    private $reflexHeaders;

    private bool $setHeaderOnly = true; // if setHeaderOnly() auto-magic is used on withStatus() calls

    private bool $isImmutable = true;

    private function __construct(\sfWebResponse $sfWebResponse)
    {
        $this->sfWebResponse = $sfWebResponse;
    }

    /**
     * @param array<string, bool> $options
     *
     * @return Response
     */
    public static function fromSfWebResponse(\sfWebResponse $sfWebResponse, array $options = []): self
    {
        $new = new static($sfWebResponse);

        if (isset($options[self::OPTION_SEND_BODY_ON_204])) {
            $new->setHeaderOnly = false;
        }

        // defaulting to mutating PSR-7-violating behavior when creating from \sfWebResponse
        if (!array_key_exists(self::OPTION_IMMUTABLE_VIOLATION, $options) || false !== $options[self::OPTION_IMMUTABLE_VIOLATION]) {
            $new->isImmutable = false;
        }

        return $new;
    }

    /**
     * @deprecated Avoid this at all costs! It only serves as a last resort!
     */
    public function getSfWebResponse(): \sfWebResponse
    {
        return $this->sfWebResponse;
    }

    public function getProtocolVersion(): string
    {
        $options = $this->sfWebResponse->getOptions();

        return (isset($options[self::SFR_HTTP_PROTOCOL_OPTION])
            && preg_match('/^HTTP\/(\d\.\d)$/i', $options[self::SFR_HTTP_PROTOCOL_OPTION], $versionMatch)) ? $versionMatch[1] : '';
    }

    /**
     * @param string $version
     *
     * @throws \ReflectionException
     *
     * @return static
     *
     * @deprecated Changes are directly applied to the adapted sfWebResponse, thus the returned object will return same value as the "immutable" original instance
     */
    public function withProtocolVersion($version): self
    {
        $options                                 = $this->sfWebResponse->getOptions();
        $options[self::SFR_HTTP_PROTOCOL_OPTION] = 'HTTP/' . $version;
        $this->retroduceOptions($options);
        $this->reflexOptions = null;    // just to satisfy \Http\Psr7Test\MessageTrait::testProtocolVersion

        return $this->getThisOrClone();
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
     *
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
        return (int)$this->sfWebResponse->getStatusCode();
    }

    /**
     * N.b. Changes are directly applied to the adapted sfWebResponse,
     *      thus the returned object will return same value as the "immutable" original instance
     *
     * @param int    $code
     * @param string $reasonPhrase
     *
     * @return static
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        Assert::integer($code);
        Assert::greaterThanEq($code, 100);
        Assert::lessThanEq($code, 599);

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

        return $this->getThisOrClone();
    }

    public function getReasonPhrase(): string
    {
        return $this->sfWebResponse->getStatusText();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getBody(): StreamInterface
    {
        if (!$this->body || !$this->body->isReadable()) {
            // Refresh from adapted sfWebRequest if stream is missing or stale
            $this->body = Utils::streamFor($this->sfWebResponse->getContent());
        }

        return $this->body;
    }

    /**
     * Unless preSend() was called the latest stream is used for underlying sfWebResponse's content
     *
     * @return static
     */
    public function withBody(StreamInterface $body): self
    {
        $new       = $this->getThisOrClone();
        $new->body = $body;
        $this->sfWebResponse->setContent((string)$body);

        $hook = $this->getBodyStreamHook();
        $hook->addBodyFromResponse($new);

        return $new;
    }

    /**
     * Mark the body if this very instance as the to be sent one, when dealing with multiple adaptions for a single sfWebResponse
     *
     * When using the immutability-mode (as PSR-7 demands) there is no bijection any more between
     *   adapted sfWebResponse and this adapter. You may have several instances due to with* methods.
     *
     * The body of the very instance where this method was called the latest will be returned when
     * sfWebResponse->send() or sfWebResponse->sendContent() were called on the adapted sfWebResponse.
     *
     * This is only a feature for the body as this a stream which may be altered separately.
     * On all other features (headers, method etc.) the latest write wins, as those are directly passed to the underlying sfWebResponse.
     *
     * If preSend() is not used, the latest attached and readable stream will be used as content
     */
    public function preSend(): void
    {
        $hook = $this->getBodyStreamHook();
        $hook->addBodyFromResponse($this);
        $hook->distinguishResponse($this);
    }

    private function getBodyStreamHook(): BodyStreamHook
    {
        $options = $this->sfWebResponse->getOptions();
        if (!isset($options[self::SFR_STREAM_HOOK_OPTION])) {
            $options[self::SFR_STREAM_HOOK_OPTION] = new BodyStreamHook($this->sfWebResponse);
            $this->retroduceOptions($options);
            $this->reflexOptions = null;    // just to satisfy \Http\Psr7Test\MessageTrait::testBody
        }

        return $options[self::SFR_STREAM_HOOK_OPTION];
    }

    /**
     * sets symfony response's options property using reflection
     *
     * @param array{http_protocol: string ,__brncBodyStreamHook: null|\brnc\Symfony1\Message\Adapter\BodyStreamHook} $options
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
        if ($this->isImmutable) {
            return clone $this;
        }

        return $this;
    }
}
