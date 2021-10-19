<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\integration;

use brnc\Symfony1\Message\Adapter\Response;
use Http\Psr7Test\ResponseIntegrationTest;

/**
 * @internal
 */
final class ResponseTest extends ResponseIntegrationTest
{
    public function createSubject()
    {
        $symfonyResponseMock = new \sfWebResponse();
        $symfonyResponseMock->prepare();

        return Response::fromSfWebResponse($symfonyResponseMock, [Response::OPTION_IMMUTABLE_VIOLATION => false]);
    }
}
