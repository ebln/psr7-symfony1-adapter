<?php
declare(strict_types = 1);

namespace brnc\Symfony1\Message\Adapter;

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

    /** @var StreamInterface|null */
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

        $headers = $this->getHeader($name);
        if (is_array($value)) {
            $headers = array_merge($headers, $value);
        } else {
            $headers[] = $value;
        }

        $this->setHeader($name, $headers);

        return $this;
    }

    /**
     * N.b. in the Response this is *not* applying the extra call to fixContentType() by setHttpHeader()
     *
     * @param string          $name
     * @param string|string[] $value
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withHeader($name, $value): self
    {
        $this->headerNames[$this->normalizeHeaderName($name)] = $name;
        $this->setHeader($name, $value);

        return $this;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body ? $this->body : stream_for('');
    }

    /**
     * Parses the protocol version from an internal symfony array
     *
     * @param array  $array
     * @param string $key
     *
     * @return string
     */
    protected function getVersionFromArray(array $array, string $key): string
    {
        return (isset($array[$key])
            && preg_match('/^HTTP\/(\d\.\d)$/i', $array[$key], $versionMatch)) ? $versionMatch[1] : '';
    }

    /**
     * Explodes a HTTP header's value to address PSR-7 arrayfied sub-value approach
     *
     * @param string $line
     *
     * @return string[]
     */
    protected function explodeHeaderLine(string $line): array
    {
        return array_map(
            function ($v) {
                return trim($v, " \t"); // https://tools.ietf.org/html/rfc7230#section-3.2.4
            },
            explode(',', $line)
        );
    }

    /**
     * @param string|string[] $value
     *
     * @return string
     */
    protected function implodeHeaders($value): string
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * TODO add header name validation! (would be only valid for shadow)
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeHeaderName(string $name): string
    {
        return str_replace('_', '-', strtolower($name));
    }
}
