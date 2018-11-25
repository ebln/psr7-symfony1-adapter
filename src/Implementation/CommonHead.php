<?php

namespace brnc\Symfony1\Message\Implementation;


use brnc\Contract\Http\Message\CommonHeadInterface;
use brnc\Symfony1\Message\Implementation\Hidden\CommonHeadTrait;

/**
 * subset of psr/http-message-implementation
 */
class CommonHead implements CommonHeadInterface
{
    use CommonHeadTrait;
}
