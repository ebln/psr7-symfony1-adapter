<?php /** @noinspection PhpUnusedParameterInspection */

/**
 * Minimal mock of symfony's sfWebResponse to enable standalone testing
 */
class sfWebResponse implements \brnc\Symfony1\Message\Obligation\SfWebResponseSubsetInterface
{
    /** @var int */
    private $code;

    /** @var string */
    private $reasonPhrase;

    /** @var array */
    private $options;

    /** @var array */
    private $headers;

    /** @var array */
    private $cookies;

    /**
     * @param mixed $dispatcher
     * @param array $options
     */
    public function __construct($dispatcher = null, $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param int         $code
     * @param string|null $reasonPhrase
     * @param string[]    $headers
     * @param array       $cookies
     */
    public function prepare($code = 200, $reasonPhrase = null, $headers = [], $cookies = [])
    {
        $this->setStatusCode($code, $reasonPhrase);
        $this->headers = $headers;
        $this->cookies = $cookies;
    }

    /** @return int */
    public function getStatusCode()
    {
        return $this->code;
    }

    /** @return string */
    public function getStatusText()
    {
        return $this->reasonPhrase;
    }

    /**
     * @param int         $code
     * @param string|null $name
     */
    public function setStatusCode($code, $name = null)
    {
        $this->code = (int)$code;

        if (null === $name) {
            switch ($this->code) {
                case 200:
                    $this->reasonPhrase = 'OK';
                    break;
                default:
                    $this->reasonPhrase = 'No reason phrase given';
            }
        }
        else {
            $this->reasonPhrase = $name;
        }
    }

    /** @return string[] */
    public function getHttpHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHttpHeader($name)
    {
        return array_key_exists($this->normalizeHeaderName($name), $this->headers);
    }

    /**
     * @param  string $name
     * @param  string $default
     *
     * @return string
     */
    public function getHttpHeader($name, $default = null)
    {
        $normalizedName = $this->normalizeHeaderName($name);

        return isset($this->headers[$normalizedName])? $this->headers[$normalizedName] : $default;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool   $replace
     */
    public function setHttpHeader($name, $value, $replace = true)
    {
        $normalizedName = $this->normalizeHeaderName($name);
        // following symfony's implementation $value === null unsets regardless of $replace
        if (null === $value) {
            unset($this->headers[$normalizedName]);

            return;
        }
        // following symfony's implementation, may be only first-written or overwritten not appended
        $isSet         = isset($this->headers[$normalizedName]);
        $isContentType = 'Content-Type' === $normalizedName;
        if (!$replace && !$isContentType && $isSet && $this->headers[$normalizedName]) {
            $value = $this->headers[$normalizedName] . ', ' . $value;
        }
        if (!$isContentType || !$isSet || $replace) {
            $this->headers[$normalizedName] = $value;
        }
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /** @return array */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @param string $name
     * @param string $value
     * @param null   $expire
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     */
    public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false,
                              $httpOnly = false
    ) {
        // skipping original verification of expiration
        $this->cookies[$name] = [
            'name'     => $name,
            'value'    => $value,
            'expire'   => $expire,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure? true : false,
            'httpOnly' => $httpOnly,
        ];
    }

    /**
     * @param  string $name Header name
     *
     * @return string Normalized header
     */
    protected function normalizeHeaderName($name)
    {
        return ucwords(str_replace(['_', ' '], '-', strtolower($name)), ['-']);
    }
}
