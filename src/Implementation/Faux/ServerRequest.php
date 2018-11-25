<?php

namespace brnc\Symfony1\Message\Implementation\Faux;


use brnc\Symfony1\Message\Falsification;
use brnc\Symfony1\Message\Implementation\SubsetServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * implements RequestInterface but body, uri, request-target, and uploaded-files
 */
class ServerRequest extends SubsetServerRequest implements ServerRequestInterface
{
    use Falsification\FalseBodyTrait;
    use Falsification\FalseRequestTrait;
    use Falsification\FalseServerRequestUploadTrait;
}
