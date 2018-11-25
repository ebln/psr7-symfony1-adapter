<?php

namespace brnc\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Falsification;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ReadMinimalRequestHeadAdapter with faked ServerRequestInterface of PSR-7 compliance
 */
class FalseReadMinimalRequestHeadAdapter extends ReadMinimalRequestHeadAdapter implements ServerRequestInterface
{
    use Falsification\FalseBodyTrait;
    use Falsification\FalseRequestTrait;
    use Falsification\FalseCommonHeadTrait;
    use Falsification\FalseServerRequestParsedBody;
    use Falsification\FalseServerRequestAttributeTrait;
    use Falsification\FalseServerRequestReadParametersTrait;
    use Falsification\FalseServerRequestUploadAndModifyParametersTrait;
}
