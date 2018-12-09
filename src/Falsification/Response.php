<?php

namespace brnc\Symfony1\Message\Falsification;

use brnc\Symfony1\Message\Adapter\Response as ResponseAdapter;
use Psr\Http\Message\ResponseInterface;

/**
 * falsified version of @see \brnc\Symfony1\Message\Adapter\Response to achieve PSR-7 compliance by cheating
 */
class Response extends ResponseAdapter implements ResponseInterface
{
    use FalseBodyTrait;
}
