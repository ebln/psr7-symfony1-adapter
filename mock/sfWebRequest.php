<?php

/** @noinspection PhpUnusedParameterInspection */

/** @noinspection ReturnTypeCanBeDeclaredInspection */

/**
 * Minimal mock of symfony's sfWebRequest to enable standalone testing
 *
 * @internal
 */
class sfWebRequest
{
    /** @var string */
    private $method;

    /**  @var string[] */
    private $pathInfoArray;

    /** @var string[] */
    private $getParameters;

    /** @var string[] */
    private $postParameters;

    /** @var array */
    private $options;

    /** @var array */
    private $requestParameters;

    /** @var string[] */
    private $cookie;

    /** @var string|null */
    private $content;

    /** @var string|null */
    private $uri;

    /** @var array[] */
    private $files;

    /**
     * dummy constructor to preserve the original signature → please initialise with prepare() afterwards!
     *
     * @param mixed $dispatcher
     * @param array $parameters
     * @param array $attributes
     * @param array $options
     */
    public function __construct($dispatcher = null, $parameters = [], $attributes = [], $options = [])
    {
        $this->options = $options;
    }

    public function prepare(
        string $method,
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $requestParameters = [],
        ?string $content = null,
        ?string $uri = null,
        ?array $files = []
    ): void {
        $this->method            = $method;
        $this->pathInfoArray     = $server;
        $this->getParameters     = $get;
        $this->postParameters    = $post;
        $this->cookie            = $cookie;
        $this->requestParameters = $requestParameters;
        $this->content           = $content;
        $this->uri               = $uri;
        $this->files             = $files;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * WARNING this mock is not checking symfony's rules of allowed methods
     *
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * @return array<string, string>
     */
    public function getPathInfoArray()
    {
        return $this->pathInfoArray;
    }

    /**
     * @param string      $name
     * @param string|null $prefix
     *
     * @return string|null
     */
    public function getHttpHeader($name, $prefix = 'HTTP')
    {
        $key = strtoupper(str_replace('-', '_', ($prefix ? $prefix . '_' : '') . $name));

        return $this->pathInfoArray[$key] ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $defaultValue
     *
     * @return string|null|mixed
     */
    public function getCookie($name, $defaultValue = null)
    {
        return $this->cookie[$name] ?? $defaultValue;
    }

    /**
     * @return array<string, array|string>
     */
    public function getGetParameters()
    {
        return $this->getParameters;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPostParameters()
    {
        return $this->postParameters;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getRequestParameters()
    {
        return $this->requestParameters;
    }

    /**
     * @return string|false
     */
    public function getContent()
    {
        return $this->content ?? false;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri ?? 'http://localhost:80/';
    }

    public function getFiles($key = null): array
    {
        if (null !== $key) {
            throw new LogicException('Only subminimal implementation.');
        }

        return $this->files;
    }
}
