<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\LazyOpenStream;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use ReflectionObject;

/**
 * TODO
 *      Cookie handling
 *          Cookie Abstraction
 *              including Header transcription
 *      Proper Interface?
 */
class Request
{
    use CommonAdapterTrait;

    /** @see https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input for reuseablity of php://input */
    public const OPTION_BODY_USE_STREAM       = 'Use php://input directly';
    public const OPTION_EXPOSE_SF_WEB_REQUEST = 'Populate attribute with sfWebRequest';
    public const ATTRIBUTE_SF_WEB_REQUEST     = 'sfWebRequest';

    /** @var bool[] */
    protected static $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];

    /** @var \sfWebRequest */
    protected $sfWebRequest;

    /** @var null|\ReflectionProperty */
    protected $reflexPathInfoArray;

    /** @var array<string, mixed> */
    protected $attributes = [];

    /** @var UriInterface */
    protected $uri;

    /**
     * @var string shadow to honour: »[…]method names are case-sensitive and thus implementations SHOULD NOT modify the given string.«
     */
    protected $method;

    private function __construct()
    {
    }

    /**
     * @param array<string, bool> $options
     *
     * @return Request
     */
    public static function fromSfWebRequest(\sfWebRequest $sfWebRequest, array $options = []): self
    {
        $new               = new static();
        $new->sfWebRequest = $sfWebRequest;

        if (isset($options[self::OPTION_BODY_USE_STREAM])) {
            $new->body = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        } else {
            $content = $sfWebRequest->getContent();
            if (false !== $content) {
                // lazy init, as getBody() defaults properly
                $new->body = stream_for($content);
            }
        }

        if (isset($options[self::OPTION_EXPOSE_SF_WEB_REQUEST])) {
            $new->attributes[self::ATTRIBUTE_SF_WEB_REQUEST] = $sfWebRequest;
        }

        $new->uri = new Uri($sfWebRequest->getUri());

        return $new;
    }

    public function getProtocolVersion(): string
    {
        return $this->getVersionFromArray($this->sfWebRequest->getPathInfoArray(), 'SERVER_PROTOCOL');
    }

    /**
     * @param string $version
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     * @throws \ReflectionException
     *
     */
    public function withProtocolVersion($version): self
    {
        $pathInfoArray                    = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray['SERVER_PROTOCOL'] = 'HTTP/' . $version;
        $this->retroducePathInfoArray($pathInfoArray);

        return $this;
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->sfWebRequest->getPathInfoArray() as $key => $value) {
            $useKey = null;
            if (0 === strpos($key, 'HTTP_')) {
                $useKey = substr($key, 5);
            } elseif (isset(self::$contentHeaders[$key])) {
                $useKey = $key;
            }

            if (null !== $useKey) {
                $headerName = $this->normalizeHeaderName($useKey);
                /* @noinspection NullCoalescingOperatorCanBeUsedInspection */
                if (isset($this->headerNames[$headerName])) {
                    $headerName = $this->headerNames[$headerName]; // return shadowed header name
                }

                $headers[$headerName] = $this->explodeHeaderLine($value);
            }
        }

        return $headers;
    }

    /**
     * @param string $name
     */
    public function getHeaderLine($name): string
    {
        $value = $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));

        return $value ?? '';
    }

    /**
     * @param string $name
     */
    public function hasHeader($name): bool
    {
        return null !== $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));
    }

    /**
     * @param string $name
     *
     * @return string[]
     */
    public function getHeader($name): array
    {
        $value = $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));

        return null === $value ? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     * @throws \ReflectionException
     *
     */
    public function withoutHeader($name): self
    {
        $keyName       = $this->getPathInfoKey($name);
        $pathInfoArray = $this->sfWebRequest->getPathInfoArray();
        unset($pathInfoArray[$keyName]);
        $this->retroducePathInfoArray($pathInfoArray);
        unset($this->headerNames[$this->normalizeHeaderName($name)]);

        return $this;
    }

    public function getMethod(): string
    {
        $method = $this->sfWebRequest->getMethod();
        if (!$this->method || $method !== strtoupper($this->method)) {
            $this->method = $method;   // overwrite capitalisation despite the »SHOULD NOT« in the PSR-7 definition
        }

        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method does not return a clone but the very
     *               same instance, due to the nature of the underlying adapted symfony object
     */
    public function withMethod($method): self
    {
        $this->method = $method;
        $this->sfWebRequest->setMethod($method);

        return $this;
    }

    /**
     * wrapper for symfony's getPathInfoArray()
     *
     * @return array<string, string> symfony's getPathInfoArray()
     */
    public function getServerParams(): array
    {
        return $this->sfWebRequest->getPathInfoArray();
    }

    /**
     * TODO: check SG-header-congruency
     *
     * @return array<string, string>
     */
    public function getCookieParams(): array
    {
        return $_COOKIE; // as getCookie() is nothing but a lookup
    }

    /**
     * @return array<string, string>
     */
    public function getQueryParams(): array
    {
        return $this->sfWebRequest->getGetParameters();
    }

    /**
     * @return array<string, string>
     */
    public function getParsedBody(): array
    {
        return $this->sfWebRequest->getPostParameters();
    }

    /**
     * @return mixed[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     *
     * @return null|mixed
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @param string     $name
     *
     * @param null|mixed $value
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method doesn't return a clone but the instance
     */
    public function withAttribute($name, $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method doesn't return a clone but the instance
     */
    public function withoutAttribute($name): self
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * @return $this In conflict with PSR-7's immutability paradigm, this method doesn't return a clone but the instance
     */
    public function withBody(StreamInterface $body): self
    {
        throw new \LogicException('Altering content is not supported by sfRequest.');
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function getRequestTarget(): string
    {
        $target = $this->uri->getPath();
        if ('' === $target) {
            $target = '/';
        }
        if ('' !== $this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * sets symfony request's pathInfoArray property using reflection
     *
     * @param array<string, string> $pathInfo
     *
     * @throws \ReflectionException
     */
    protected function retroducePathInfoArray(array $pathInfo): void
    {
        if (null === $this->reflexPathInfoArray) {
            $reflexiveWebRequest       = new ReflectionObject($this->sfWebRequest);
            $this->reflexPathInfoArray = $reflexiveWebRequest->getProperty('pathInfoArray');
            $this->reflexPathInfoArray->setAccessible(true);
        }

        $this->reflexPathInfoArray->setValue($this->sfWebRequest, $pathInfo);
    }

    /**
     * injects a header into symfony's pathInfoArray via setPathInfoArray()'s reflection
     *
     * @param string|string[] $value
     *
     * @throws \ReflectionException
     */
    protected function setHeader(string $name, $value): void
    {
        $keyName                 = $this->getPathInfoKey($name);
        $pathInfoArray           = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray[$keyName] = $this->implodeHeaders($value);
        $this->retroducePathInfoArray($pathInfoArray);
    }

    /**
     * get the array key resp. to pathInfoArray from the header field name
     */
    protected function getPathInfoKey(string $name): string
    {
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (!isset(self::$contentHeaders[$keyName])) {
            $keyName = 'HTTP_' . $keyName;
        }

        return $keyName;
    }

    /**
     * in order to also obtain content headers via getHttpHeader()
     */
    protected function getPathInfoPrefix(string $name): ?string
    {
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (isset(self::$contentHeaders[$keyName])) {
            return null;
        }

        return 'HTTP';
    }
}
