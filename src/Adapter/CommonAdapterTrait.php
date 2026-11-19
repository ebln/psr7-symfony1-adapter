<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Utility\Assert;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

/**
 * collects common behaviour of request and response
 *
 * @internal
 */
trait CommonAdapterTrait
{
    /**
     * @var string[]
     *
     * shadow to honour: »[…]preserve the exact case in which headers were originally specified.«
     */
    private array            $headerNames = [];

    /**
     * @var array<string, string[]> by normalized header name
     *
     * shadow of the values given to withHeader() and withAddedHeader(), as symfony keeps one joined line per header
     */
    private array            $headerValues = [];
    private ?StreamInterface $body         = null;

    /**
     * Symfony 1 holds one line per header name, so all values are joined into one line with `, `.
     *
     * Known limitation of the Response: one Set-Cookie header per response. Several Set-Cookie values are joined
     * into one line, and browsers keep at most the first cookie. Use sfWebResponse::setCookie(), or the
     * ResponseTranscriptor with a cookie transcriptor, instead. See README, "Known limitations".
     *
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
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
        $new->rememberHeaderValues($name, $headers);

        return $new;
    }

    /**
     * N.b. in the Response this is *not* applying the extra call to fixContentType() by setHttpHeader()
     *
     * Symfony 1 holds one line per header name, so an array of values is joined into one line with `, `.
     *
     * Known limitation of the Response: one Set-Cookie header per response. Several Set-Cookie values are joined
     * into one line, and browsers keep at most the first cookie. Use sfWebResponse::setCookie(), or the
     * ResponseTranscriptor with a cookie transcriptor, instead. See README, "Known limitations".
     *
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public function withHeader($name, $value)
    {
        Assert::stringNotEmpty($name);
        $new                                                = $this->getThisOrClone();
        $new->headerNames[$new->normalizeHeaderName($name)] = $name;
        $new->setHeader($name, $value);
        $new->rememberHeaderValues($name, $value);

        return $new;
    }

    /** @throws \InvalidArgumentException */
    public function getBody(): StreamInterface
    {
        return $this->body ?? Utils::streamFor();
    }

    /**
     * Returns the values of one header line held by symfony
     *
     * A line is not split on commas, as many values contain commas (dates, User-Agent, Set-Cookie).
     * The values given to withHeader() or withAddedHeader() are returned while symfony still holds their joined line.
     * Each value is trimmed of spaces and tabs (https://tools.ietf.org/html/rfc7230#section-3.2.4).
     *
     * @param string      $line     the line as symfony returns it
     * @param null|string $heldLine the line as symfony holds it, if symfony changes it on return
     *                              (sfWebRequest::getHttpHeader() strips slashes)
     *
     * @return string[]
     */
    private function getHeaderValues(string $name, string $line, ?string $heldLine = null): array
    {
        $values = $this->headerValues[$this->normalizeHeaderName($name)] ?? null;
        if (null === $values || $this->joinHeaderValues($values) !== ($heldLine ?? $line)) {
            $values = [$line];
        }

        return array_map(
            static function (string $value): string {
                return trim($value, " \t");
            },
            $values
        );
    }

    /** @param string|string[] $value */
    private function rememberHeaderValues(string $name, $value): void
    {
        $this->headerValues[$this->normalizeHeaderName($name)] = is_array($value) ? array_values($value) : [$value];
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

        return is_array($value) ? $this->joinHeaderValues($value) : $value;
    }

    /** @param string[] $values */
    private function joinHeaderValues(array $values): string
    {
        return implode(', ', $values);
    }

    private function normalizeHeaderName(string $name): string
    {
        return str_replace('_', '-', strtolower($name));
    }
}
