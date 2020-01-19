<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Exception\InvalidTypeException;
use brnc\Symfony1\Message\Exception\LogicException;
use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use ReflectionObject;

/**
 * TODO
 *      Cookie handling
 *          Cookie Abstraction
 *              including Header transcription
 */
class Request implements ServerRequestInterface
{
    use CommonAdapterTrait;

    /** @see https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input for reuseablity of php://input */
    public const OPTION_BODY_USE_STREAM       = 'Use php://input directly';
    public const OPTION_EXPOSE_SF_WEB_REQUEST = 'Populate attribute with sfWebRequest';
    public const OPTION_IMMUTABLE_VIOLATION   = 'Violate PSR-7 as this is an adapter acting on the underlying sfWebRequest';
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

    /** @var null|array|false|object → false indicated non-initialization in order to fallback to sfRequest, while null overrides sfRequest */
    protected $parsedBody;

    /** @var array */
    protected $cookieParams = [];

    /** @var bool */
    protected $isImmutable = true;

    /** @var array */
    protected $queryParams;

    /** @var UploadedFileInterface[] */
    protected $uploadedFiles;

    /**
     * @var string shadow to honour: »[…]method names are case-sensitive and thus implementations SHOULD NOT modify the given string.«
     */
    protected $method;

    private function __construct()
    {
    }

    public function __clone()
    {
        $this->uri                 = clone $this->uri;
        $this->body                = $this->body ? clone $this->body : $this->body;
        $this->sfWebRequest        = clone $this->sfWebRequest;
        $this->reflexPathInfoArray = null;
        $this->parsedBody          = is_object($this->parsedBody) ? clone $this->parsedBody : $this->parsedBody;
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

        // default to non-immutable PSR-7 violating behavior when creating from \sfWebRequest
        if (!array_key_exists(self::OPTION_IMMUTABLE_VIOLATION, $options) || false !== $options[self::OPTION_IMMUTABLE_VIOLATION]) {
            $new->isImmutable  = false;
            $new->parsedBody   = false;
            $new->cookieParams = null;
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
     * @throws \ReflectionException
     */
    public function withProtocolVersion($version): self
    {
        $new                              = $this->getNew();
        $pathInfoArray                    = $new->sfWebRequest->getPathInfoArray();
        $pathInfoArray['SERVER_PROTOCOL'] = 'HTTP/' . $version;
        $new->retroducePathInfoArray($pathInfoArray);

        return $new;
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
     */
    public function getHeaderLine($name): string
    {
        $value = $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));

        return $value ?? '';
    }

    /**
     * @param string $name
     *
     * @throws \ReflectionException
     */
    public function withoutHeader($name): self
    {
        $new           = $this->getNew();
        $keyName       = $new->getPathInfoKey($name);
        $pathInfoArray = $new->sfWebRequest->getPathInfoArray();
        unset($pathInfoArray[$keyName]);
        $new->retroducePathInfoArray($pathInfoArray);
        unset($new->headerNames[$new->normalizeHeaderName($name)]);

        return $new;
    }

    public function withBody(StreamInterface $body): self
    {
        $new       = $this->getNew(true);
        $new->body = $body;

        return $new;
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
     * TODO!
     *
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        return $this->getNew();
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
     */
    public function withMethod($method): self
    {
        if (!is_string($method)) {
            InvalidTypeException::throwStringExpected($method);
        }

        $new         = $this->getNew();
        $new->method = $method;
        $new->sfWebRequest->setMethod($method);

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return $this->getNew();
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
     * perhaps-do: check SG-header-congruency
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams ?? $_COOKIE; // as getCookie() in sfWebRequest is nothing but a lookup
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $new               = $this->getNew(true);
        $new->cookieParams = $cookies;

        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams ?? $this->sfWebRequest->getGetParameters();
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query): self
    {
        $new              = $this->getNew(true);
        $new->queryParams = $query;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles()
    {
        if (!$this->uploadedFiles) {
            foreach ($this->sfWebRequest->getFiles() as $file) {
                $this->uploadedFiles[] = new UploadedFile(
                    $file['tmp_name'],
                    (int)$file['size'],
                    (int)$file['error'],
                    $file['name'],
                    $file['type']
                );
            }
        }

        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        $new                = $this->getNew(false);
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * @return null|array|object
     */
    public function getParsedBody()
    {
        return false === $this->parsedBody ? $this->sfWebRequest->getPostParameters() : $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data): self
    {
        if (!is_array($data) && !is_object($data) && null !== $data) {
            InvalidTypeException::throwStringOrArrayOrNullExpected($data);
        }

        $new             = $this->getNew(false);
        $new->parsedBody = $data;

        return $new;
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
     * @param null|mixed $value
     */
    public function withAttribute($name, $value): self
    {
        $new                    = $this->getNew();
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @param string $name
     */
    public function withoutAttribute($name): self
    {
        $new = $this->getNew();

        unset($new->attributes[$name]);

        return $new;
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
     *
     * @param string $name
     */
    protected function getPathInfoPrefix($name): ?string
    {
        $this->validateHeaderName($name);
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (isset(self::$contentHeaders[$keyName])) {
            return null;
        }

        return 'HTTP';
    }

    protected function getNew(bool $failOnMutation = false): self
    {
        if (!$this->isImmutable) {
            if ($failOnMutation) {
                LogicException::throwAdaptingSymfony();
            }

            return $this;
        }

        return clone $this;
    }
}
