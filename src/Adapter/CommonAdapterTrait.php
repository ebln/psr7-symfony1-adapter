<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Exception\InvalidTypeException;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\StreamInterface;

/**
 * collects common behaviour of request and response
 */
trait CommonAdapterTrait
{
    /**
     * @var string[]
     *
     * shadow to honour: »[…]preserve the exact case in which headers were originally specified.«
     */
    protected $headerNames = [];

    /** @var null|StreamInterface */
    protected $body;

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withAddedHeader($name, $value): self
    {
        if (!$this->hasHeader($name)) {
            // to preserve the original header name
            return $this->withHeader($name, $value);
        }

        $new     = $this->getNew(false);
        $headers = $new->getHeader($name);
        if (is_array($value)) {
            $headers = array_merge($headers, $value);
        } else {
            $headers[] = $value;
        }

        $new->setHeader($name, $headers);

        return $new;
    }

    /**
     * N.b. in the Response this is *not* applying the extra call to fixContentType() by setHttpHeader()
     *
     * @param string          $name
     * @param string|string[] $value
     */
    public function withHeader($name, $value): self
    {
        /** @var CommonAdapterTrait $new */
        $new                                                = $this->getNew(false);
        $new->headerNames[$new->normalizeHeaderName($name)] = $name;
        $new->setHeader($name, $value);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body ?? stream_for();
    }

    /**
     * Parses the protocol version from an internal symfony array
     *
     * @param array<string, string> $array
     */
    protected function getVersionFromArray(array $array, string $key): string
    {
        return (isset($array[$key])
            && preg_match('/^HTTP\/(\d\.\d)$/i', $array[$key], $versionMatch)) ? $versionMatch[1] : '';
    }

    /**
     * Explodes a HTTP header's value to address PSR-7 arrayified sub-value approach
     *
     * @return string[]
     */
    protected function explodeHeaderLine(string $line): array
    {
        return array_map(
            static function ($element) {
                return trim($element, " \t"); // https://tools.ietf.org/html/rfc7230#section-3.2.4
            },
            explode(',', $line)
        );
    }

    /**
     * @param string|string[] $value
     */
    protected function implodeHeaders($value): string
    {
        $isArray = is_array($value);
        if (!is_string($value) && !$isArray) { // perhaps-do: improve validation
            InvalidTypeException::throwStringOrArrayOfStringsExpected($value);
        }
        if ($isArray && empty($value)) {
            InvalidTypeException::throwNotEmptyExpected();
        }

        return $isArray ? implode(',', $value) : $value;
    }

    /**
     * @param string $name
     */
    protected function normalizeHeaderName($name): string
    {
        $this->validateHeaderName($name);

        return str_replace('_', '-', strtolower($name));
    }

    protected function validateHeaderName($name): void
    {
        if (!is_string($name)) { // perhaps-do: improve validation according to https://tools.ietf.org/html/rfc7230#section-3.2
            InvalidTypeException::throwStringExpected($name);
        }
        if ('' === $name) {
            InvalidTypeException::throwNotEmptyExpected();
        }
    }
}
