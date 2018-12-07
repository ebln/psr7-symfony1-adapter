<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Response;
use PHPUnit\Framework\TestCase;

class ResponseBasicTest extends TestCase
{
    public function testProtocolVersion()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse();
        $this->assertSame('', $response->getProtocolVersion());
        $this->assertSame([], $symfony->getOptions());

        $response = $response->withProtocolVersion('1.1');
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    public function testPresetProtocolVersion()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse(200, null, [], [], ['http_protocol' => 'HTTP/1.0']);
        $this->assertSame('1.0', $response->getProtocolVersion());
        $response = $response->withProtocolVersion('1.1');
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame(['http_protocol' => 'HTTP/1.1'], $symfony->getOptions());
    }

    public function testStatus()
    {
        /**
         * @var Response       $response
         * @var \sfWebResponse $symfony
         */
        list($response, $symfony) = $this->createResponse(204);
        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('No reason phrase given', $response->getReasonPhrase());
        $this->assertSame(204, $symfony->getStatusCode());
        $this->assertSame('No reason phrase given', $symfony->getStatusText());

        $response = $response->withStatus('200');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame(200, $symfony->getStatusCode());
        $this->assertSame('OK', $symfony->getStatusText());

        $response = $response->withStatus('400', '*** Bad Request ***');
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('*** Bad Request ***', $response->getReasonPhrase());
        $this->assertSame(400, $symfony->getStatusCode());
        $this->assertSame('*** Bad Request ***', $symfony->getStatusText());
    }

    /**
     * @param int         $code
     * @param string|null $reasonPhrase
     * @param string[]    $headers
     * @param array       $cookies
     * @param array       $options
     *
     * @return array
     */
    private function createResponse(
        $code = 200,
        $reasonPhrase = null,
        $headers = [],
        $cookies = [],
        array $options = []
    ) {
        $symfonyResponseMock = new \sfWebResponse(null, $options);
        $symfonyResponseMock->prepare($code, $reasonPhrase, $headers, $cookies);

        return [new Response($symfonyResponseMock), $symfonyResponseMock];
    }
}
