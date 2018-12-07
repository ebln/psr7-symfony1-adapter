<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;

class ServerRequestBasicTest extends TestCase
{

    public function testConstrucWithAttribute()
    {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare('GET');
        $request = new Request($symfonyRequestMock, true);
        $this->assertSame($symfonyRequestMock, $request->getAttribute(Request::ATTRIBUTE_SF_WEB_REQUEST));
        $this->assertSame(spl_object_hash($symfonyRequestMock), spl_object_hash($request->getAttribute(Request::ATTRIBUTE_SF_WEB_REQUEST)));
    }
}
