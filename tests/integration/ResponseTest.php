<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\integration;

use brnc\Symfony1\Message\Adapter\Response;
use Http\Psr7Test\ResponseIntegrationTest;

class ResponseTest extends ResponseIntegrationTest
{
    public function createSubject()
    {
        $symfonyResponseMock = new \sfWebResponse();
        $symfonyResponseMock->prepare();

        $response = Response::fromSfWebResponse($symfonyResponseMock, [Response::OPTION_SEND_BODY_ON_204 => false]);

        return $response;
    }
}
