<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Exception\InvalidTypeException;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\StreamInterface;
use Webmozart\Assert\Assert;

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
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        if (!$this->hasHeader($name)) {
            // to preserve the original header name
            return $this->withHeader($name, $value);
        }

        $new     = $this->getThisOrClone();
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
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        $new                                                = $this->getThisOrClone();
        /* @var CommonAdapterTrait $new */
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
     * Explodes a HTTP header's value to address PSR-7 array-fied sub-value approach
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
        if (!is_string($value)) {
            Assert::allStringNotEmpty($value);
            Assert::notEmpty($value);
            // perhaps rethrow to InvalidTypeException
        }

        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * @param string $name
     */
    protected function normalizeHeaderName($name): string
    {
        $this->validateHeaderName($name);

        return str_replace('_', '-', strtolower($name));
    }

    /**
     * @param mixed $name
     *
     * @param-out string $name
     *
     * @throws InvalidTypeException
     */
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
