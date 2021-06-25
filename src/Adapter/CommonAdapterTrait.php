<?php

/** @noinspection ReturnTypeCanBeDeclaredInspection */

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Utillity\Assert;
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
    private array            $headerNames = [];
    private ?StreamInterface $body        = null;

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     *
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        Assert::stringNotEmpty($name);

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
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        Assert::stringNotEmpty($name);
        $new = $this->getThisOrClone();
        $new->headerNames[$new->normalizeHeaderName($name)] = $name;
        $new->setHeader($name, $value);

        return $new;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getBody(): StreamInterface
    {
        return $this->body ?? stream_for();
    }

    /**
     * Explodes a HTTP header's value to address PSR-7 array-fied sub-value approach
     *
     * @return string[]
     */
    private function explodeHeaderLine(string $line): array
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
     *
     * @throws \InvalidArgumentException
     */
    private function implodeHeaders($value): string
    {
        if (!is_string($value)) {
            Assert::allStringNotEmpty($value);
            Assert::notEmpty($value);
        }

        return is_array($value) ? implode(',', $value) : $value;
    }

    private function normalizeHeaderName(string $name): string
    {
        return str_replace('_', '-', strtolower($name));
    }
}
