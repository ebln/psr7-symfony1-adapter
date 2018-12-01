<?php


namespace brnc\Symfony1\Message\Adapter;

/**
 * @method hasHeader()
 * @method getHeader()
 * @method setHeader()
 */
trait CommonAdapterTrait
{
    /**
     * @var string[]
     *
     * shadow to honour: »[…]preserve the exact case in which headers were originally specified.«
     */
    protected $headerNames = [];

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return $this In conflict with PSR-7's immutability paradigm, this method return not a clone but the very same
     *               instance due to the nature of the underlying adapted symfony object
     */
    public function withAddedHeader($name, $value)
    {
        if (!$this->hasHeader($name)) {
            // to preserve the original header name
            return $this->withHeader($name, $value);
        }

        $headers = $this->getHeader($name);
        if (is_array($value)) {
            $headers = array_merge($headers, $value);
        }
        else {
            $headers[] = $headers;
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
     * @return $this In conflict with PSR-7's immutability paradigm, this method return not a clone but the very same
     *               instance due to the nature of the underlying adapted symfony object
     */
    public function withHeader($name, $value)
    {
        $this->headerNames[$this->normalizeHeaderName($name)] = $name;
        $this->setHeader($name, $value);

        return $this;
    }

    /**
     * Parsed the protocal version from an internal symfony array
     *
     * @param array  $array
     * @param string $key
     *
     * @return string
     */
    public function getVersionFromArray(array $array, $key)
    {
        return (isset($array[$key])
                && preg_match('/^HTTP\/(\d\.\d)$/i', $array[$key], $versionMatch))? $versionMatch[1] : '';
    }

    /**
     * Explodes a HTTP header's value to address PSR-7 arrayfied sub-value approach
     *
     * @param string $line
     *
     * @return string[]
     */
    protected function explodeHeaderLine($line)
    {
        return array_map(function($v) {
            return trim($v, " \t"); // https://tools.ietf.org/html/rfc7230#section-3.2.4
        }, explode(',', $line));
    }

    /**
     * TODO:
     *
     * @param string|string[] $value
     *
     * @return string
     */
    protected function implodeHeaders($value)
    {
        return is_array($value)? implode(',', $value) : $value;
    }

    /**
     * TODO had header name validation! (would be only valid for shadow)
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeHeaderName($name)
    {
        return str_replace('_', '-', strtolower($name));
    }
}
