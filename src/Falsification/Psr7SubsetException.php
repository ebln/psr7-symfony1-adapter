<?php

namespace brnc\Symfony1\Message\Falsification;

/**
 * this allows forgery and make the interfaces of consumers more permissive
 */
class Psr7SubsetException extends \BadMethodCallException
{
    const DEFAULT_MSG = '¯\_(ツ)_/¯ This class is only implementing a subset of PSR-7! ごめんなさい';

    /**
     * Psr7SubsetException constructor.
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
