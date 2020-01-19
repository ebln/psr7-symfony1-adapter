<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\integration;

use Http\Psr7Test\ServerRequestIntegrationTest;
use brnc\Symfony1\Message\Adapter\Request;

class ServerRequestTest extends ServerRequestIntegrationTest
{
    public function createSubject()
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET', $_SERVER, [], [], [], [], null, '/');
        $request = Request::fromSfWebRequest($symfonyRequestMock, [/*Request::OPTION_IMMUTABLE_VIOLATION => false*/]);

        return $request;
    }
}
