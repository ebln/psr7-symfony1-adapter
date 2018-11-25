<?php

namespace brnc\Symfony1\Message\Implementation\Faux;

use brnc\Symfony1\Message\Falsification;
use brnc\Symfony1\Message\Implementation\SubsetRequest;
use Psr\Http\Message\RequestInterface;

/**
 * implements RequestInterface but body, uri and request-target
 */
class Request extends SubsetRequest implements RequestInterface
{
    use Falsification\FalseBodyTrait;
    use Falsification\FalseRequestTrait;
}
