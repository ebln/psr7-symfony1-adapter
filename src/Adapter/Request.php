<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Exception\InvalidTypeException;
use brnc\Symfony1\Message\Exception\LogicException;
use brnc\Symfony1\Message\Utillity\Assert;
use GuzzleHttp\Psr7\CachingStream;
use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\UploadedFile;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use ReflectionObject;

/**
 * TODO
 *      Iterate on withUri
 *      Cookie handling
 *          Cookie Abstraction
 *              including Header transcription
 *
 * @psalm-consistent-constructor
 */
class Request implements ServerRequestInterface
{
    use CommonAdapterTrait;

    /** @see https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input for reuseablity of php://input */
    public const OPTION_BODY_USE_STREAM     = 'Use php://input directly'; // Uses a stream on php://input instead of creating one over sfWebRequest::getContent()
    public const OPTION_IMMUTABLE_VIOLATION = 'Return mutated self';      // Violates PSR-7's immutability, as this is an adapter acting on the underlying sfWebRequest

    /** @var bool[] */
    private static $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];

    /** @var \sfWebRequest */
    private $sfWebRequest;

    /** @var null|\ReflectionProperty */
    private $reflexPathInfoArray;

    /** @var array<string, mixed> */
    private array $attributes = [];

    private UriInterface $uri;

    /** @var null|array<array-key, mixed>|false|object → false indicated non-initialization in order to fallback to sfRequest, while null overrides sfRequest */
    private $parsedBody;

    /** @var null|array<array-key, mixed>|string[] */
    private ?array $cookieParams = [];

    private bool $isImmutable = true;

    /** @var null|array<array-key, mixed> */
    private $queryParams;

    /** @var array<array-key, mixed> */
    private array $uploadedFiles = [];

    private bool $initialisedUploads = false;

    /** @var null|mixed|string */
    private $requestTarget;

    /** @var null|string shadow to honour: »[…]method names are case-sensitive and thus implementations SHOULD NOT modify the given string.« */
    private $method;

    private function __construct(\sfWebRequest $sfWebRequest)
    {
        $this->sfWebRequest = $sfWebRequest;
        $this->uri          = new Uri($sfWebRequest->getUri());
    }

    /**
     * maybe you also want to
     *  clone $this->sfWebRequest
     *  and unset $this->reflexPathInfoArray
     */
    public function __clone()
    {
        $this->uri        = clone $this->uri;
        $this->body       = $this->body ? clone $this->body : $this->body;
        $this->parsedBody = is_object($this->parsedBody) ? clone $this->parsedBody : $this->parsedBody;
    }

    /**
     * @param array<string, bool> $options
     *
     * @throws \InvalidArgumentException
     *
     * @return Request
     */
    public static function fromSfWebRequest(\sfWebRequest $sfWebRequest, array $options = []): self
    {
        $new = new static($sfWebRequest);
        if (isset($options[self::OPTION_BODY_USE_STREAM])) {
            $new->body = new CachingStream(new LazyOpenStream('php://input', 'r+'));
        } else {
            $content = $sfWebRequest->getContent();
            if (false !== $content) {
                // lazy init, as getBody() defaults properly to an empty body using stream_for()
                $new->body = Utils::streamFor($content);
            }
        }

        // defaulting to mutating PSR-7-violating behavior when creating from \sfWebRequest
        if (!array_key_exists(self::OPTION_IMMUTABLE_VIOLATION, $options) || false !== $options[self::OPTION_IMMUTABLE_VIOLATION]) {
            $new->isImmutable  = false;
            $new->parsedBody   = false;
            $new->cookieParams = null;
        }

        return $new;
    }

    /**
     * @deprecated Avoid this at all costs! It only serves as a last resort!
     */
    public function getSfWebRequest(): \sfWebRequest
    {
        return $this->sfWebRequest;
    }

    public function getProtocolVersion(): string
    {
        $pathInfo = $this->sfWebRequest->getPathInfoArray();

        return (isset($pathInfo['SERVER_PROTOCOL'])
            && preg_match('/^HTTP\/(\d\.\d)$/i', $pathInfo['SERVER_PROTOCOL'], $versionMatch)) ? $versionMatch[1] : '';
    }

    /**
     * @param string $version
     *
     * @throws \ReflectionException
     *
     * @return static
     *
     * @deprecated Will modify sfWebRequest even though it has no intrinsic support for this
     */
    public function withProtocolVersion($version): self
    {
        // can be altered even though underlying sfWebRequest does not support this
        $new                              = $this->getThisOrClone();
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
     *
     * @throws \InvalidArgumentException
     */
    public function hasHeader($name): bool
    {
        Assert::stringNotEmpty($name);

        return null !== $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    public function getHeader($name): array
    {
        Assert::stringNotEmpty($name);
        $value = $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));

        return null === $value ? [] : $this->explodeHeaderLine($value);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     */
    public function getHeaderLine($name): string
    {
        Assert::stringNotEmpty($name);
        $value = $this->sfWebRequest->getHttpHeader($name, $this->getPathInfoPrefix($name));

        return $value ?? '';
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     *
     * @return static
     *
     * @deprecated Will modify sfWebRequest even though it has no intrinsic support for this
     */
    public function withoutHeader($name): self
    {
        Assert::stringNotEmpty($name);
        // can be altered even though underlying sfWebRequest does not support this
        $new           = $this->getThisOrClone();
        $keyName       = $new->getPathInfoKey($name);
        $pathInfoArray = $new->sfWebRequest->getPathInfoArray();
        unset($pathInfoArray[$keyName]);
        $new->retroducePathInfoArray($pathInfoArray);
        unset($new->headerNames[$new->normalizeHeaderName($name)]);

        return $new;
    }

    /**
     * @deprecated Warning: Will not alter sfWebRequest! Won't throw exception in Symfony compatibility mode, to support modifications via middlewares
     *
     * @return static
     */
    public function withBody(StreamInterface $body): self
    {
        $new       = $this->getThisOrClone();
        $new->body = $body;

        return $new;
    }

    public function getRequestTarget(): string
    {
        if ($this->requestTarget) {
            return (string)$this->requestTarget;
        }

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
     * @param mixed|string|UriInterface $requestTarget
     *
     * @return static
     */
    public function withRequestTarget($requestTarget): self
    {
        $new                = $this->getThisOrClone();
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
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
     * @throws InvalidTypeException
     *
     * @return static
     */
    public function withMethod($method): self
    {
        Assert::string($method);
        $new         = $this->getThisOrClone();
        $new->method = $method;
        $new->sfWebRequest->setMethod($method);

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @param bool $preserveHost
     *
     * @throws LogicException
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     *
     * @return static
     *
     * @deprecated Will not alter sfWebRequest! Will crash on Symfony compatibility mode if `$preserveHost === true`!
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $new      = $preserveHost ? $this->getThisOrClone() : $this->getCloneOrDie(); // as the rewrite to HTTP_HOST et al. will not be done
        $new->uri = $uri;

        // TODO redo this & add Host to top logic to setHeader()
        if ((!$preserveHost || !$this->hasHeader('Host')) && ('' !== $uri->getHost())) {
            $headerName = $this->normalizeHeaderName('host');
            $headerName = $this->headerNames[$headerName] ?? $headerName;

            $new->setHeader($headerName, $uri->getHost() . ($uri->getPort() ? (':' . (string)$uri->getPort()) : ''));
        }

        return $new;
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
     *
     * @return array<array-key, mixed>|string[]
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams ?? $_COOKIE; // as getCookie() in sfWebRequest is nothing but a lookup
    }

    /**
     * @param array<array-key, mixed>|string[] $cookies
     *
     * @throws LogicException
     *
     * @return static
     *
     * @deprecated Will not alter sfWebRequest! Will crash on Symfony compatibility mode!
     */
    public function withCookieParams(array $cookies): self
    {
        $new               = $this->getCloneOrDie();
        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * @return array<array-key, array<array-key, mixed>|mixed|string>
     */
    public function getQueryParams(): array
    {
        return $this->queryParams ?? $this->sfWebRequest->getGetParameters();
    }

    /**
     * @param array<array-key, mixed> $query
     *
     * @throws LogicException
     *
     * @return static
     *
     * @deprecated Will not alter sfWebRequest! Will crash on Symfony compatibility mode!
     */
    public function withQueryParams(array $query): self
    {
        $new              = $this->getCloneOrDie();
        $new->queryParams = $query;

        return $new;
    }

    /**
     * @throws \LogicException
     *
     * @return array<array-key, mixed>
     */
    public function getUploadedFiles(): array
    {
        if (!$this->initialisedUploads) {
            $this->addUploadedFiles($this->sfWebRequest->getFiles(), []);
        }

        return $this->uploadedFiles;
    }

    /**
     * @param array<array-key, mixed> $uploadedFiles
     *
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        // CAVEAT: can be altered here though the underlying sfWebRequest will not be modified!
        $new                = $this->getThisOrClone();
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * @return null|array<array-key, mixed>|object
     */
    public function getParsedBody()
    {
        if (false === $this->parsedBody) {
            $this->parsedBody = $this->sfWebRequest->getPostParameters();
        }

        return $this->parsedBody;
    }

    /**
     * @param null|array<array-key, mixed>|object $data
     *
     * @throws InvalidTypeException
     *
     * @return static
     */
    public function withParsedBody($data): self
    {
        if (!is_object($data)) {
            Assert::nullOrIsArray($data);
        }
        // CAVEAT: can be altered here though the underlying sfWebRequest will not be modified!
        $new             = $this->getThisOrClone();
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
     *
     * @return static
     */
    public function withAttribute($name, $value): self
    {
        $new                    = $this->getThisOrClone();
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function withoutAttribute($name): self
    {
        $new = $this->getThisOrClone();
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
    private function retroducePathInfoArray(array $pathInfo): void
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
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    private function setHeader(string $name, $value): void
    {
        $keyName                 = $this->getPathInfoKey($name);
        $pathInfoArray           = $this->sfWebRequest->getPathInfoArray();
        $pathInfoArray[$keyName] = $this->implodeHeaders($value);
        $this->retroducePathInfoArray($pathInfoArray);
    }

    /**
     * get the array key resp. to pathInfoArray from the header field name
     */
    private function getPathInfoKey(string $name): string
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
    private function getPathInfoPrefix($name): ?string
    {
        $keyName = strtoupper(str_replace('-', '_', $name));
        if (isset(self::$contentHeaders[$keyName])) {
            return null;
        }

        return 'HTTP';
    }

    /**
     * @param array<array-key, mixed>     $files
     * @param array<array-key, array-key> $keys
     */
    private function addUploadedFiles(array $files, array $keys): void
    {
        if (isset($files['tmp_name']) && array_key_exists('size', $files) && array_key_exists('error', $files) && count($keys)) {
            Assert::string($files['tmp_name']);
            /** @psalm-var array{tmp_name:string,size:int,error:int,name:null|string,type:null|string} $files */
            $levels = new UploadedFile(
                $files['tmp_name'],
                (int)$files['size'],
                (int)$files['error'],
                $files['name'] ?? null,
                $files['type'] ?? null
            );
            foreach (array_reverse($keys) as $key) {
                $levels = [$key => $levels];
            }
            $this->uploadedFiles = array_merge_recursive($this->uploadedFiles, $levels);

            return;
        }

        foreach ($files as $key => $fileArray) {
            Assert::isArray($fileArray);
            $keysCopy   = $keys;
            $keysCopy[] = $key;
            $this->addUploadedFiles($fileArray, $keysCopy);
        }
    }

    /**
     * @throws LogicException
     *
     * @return static
     */
    private function getCloneOrDie(): self
    {
        if (!$this->isImmutable) {
            LogicException::throwAdaptingSymfony();
        }

        return clone $this;
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
