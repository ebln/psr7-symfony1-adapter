<?php /** @noinspection PhpUnusedParameterInspection */

/**
 * Minimal mock of symfony's sfWebRequest to enable standalone testing
 */
class sfWebRequest implements \brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface
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

    /**
     * @param string $method
     * @param array  $server
     * @param array  $get
     * @param array  $post
     * @param array  $cookie
     * @param array  $requestParameters
     */
    public function prepare(
        $method,
        array $server = [],
        array $get = [],
        array $post = [],
        array $cookie = [],
        array $requestParameters = []
    ) {
        $this->method            = $method;
        $this->pathInfoArray     = $server;
        $this->getParameters     = $get;
        $this->postParameters    = $post;
        $this->requestParameters = $requestParameters;
        $this->cookie            = $cookie;
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
     * @return string
     */
    public function setMethod($method)
    {
        return $this->method = strtoupper($method);
    }

    /**
     * @return array
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

        return isset($this->pathInfoArray[$key]) ? $this->pathInfoArray[$key] : null;
    }

    /**
     * @param string $name
     * @param mixed  $defaultValue
     *
     * @return string|null|mixed
     */
    public function getCookie($name, $defaultValue = null)
    {
        return isset($this->cookie[$name]) ? $this->cookie[$name] : $defaultValue;
    }

    /**
     * @return array
     */
    public function getGetParameters()
    {
        return $this->getParameters;
    }

    /**
     * @return array
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
}
