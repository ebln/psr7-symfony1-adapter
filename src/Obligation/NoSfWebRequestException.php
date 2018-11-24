<?php

namespace brnc\Symfony1\Message\Obligation;

class NoSfWebRequestException extends \InvalidArgumentException
{
    const DEFAULT_MSG = 'Expected sfWebRequest as argument!';

    /**
     * NoSfWebRequestException constructor.
     *
     * @param string                     $message
     * @param int                        $code
     * @param \Throwable|\Exception|null $previous
     */
    public function __construct($message = self::DEFAULT_MSG, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
