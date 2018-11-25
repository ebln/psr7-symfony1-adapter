<?php

namespace brnc\Symfony1\Message\Implementation\Faux;

use brnc\Symfony1\Message\Falsification\FalseBodyTrait;
use brnc\Symfony1\Message\Implementation\CommonHead;
use Psr\Http\Message\MessageInterface;

/**
 * implements MessageInterface but getBody() and withBody()
 */
class Message extends CommonHead implements MessageInterface
{
    use FalseBodyTrait;
}
