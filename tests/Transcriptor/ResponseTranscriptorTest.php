<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Transcriptor;

use brnc\Symfony1\Message\Exception\LogicException;
use brnc\Symfony1\Message\Transcriptor\ResponseTranscriptor;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ResponseTranscriptorTest extends TestCase
{
    public function testTranscribeFailCookies(): void
    {
        $this->expectException(LogicException::class);
        $psr7Response = new Response(203);
        $psr7Response = $psr7Response->withAddedHeader('Set-Cookie', 'token=1337; domain=cookie.test; path=/; expires=Sun, 12 Dec 2025 13:37:42 GMT; SameSite=Lax; secure');
        $mockResponse = $this->createSfWebResponse(
            503,
            null,
            ['X-Preset-Header' => 'Set sfWebResponse'],
            ['sf-default'      => ['name' => 'sf-default', 'value' => 'preset', 'expire' => null, 'path' => '/', 'domain' => '*', 'secure' => false, 'httpOnly' => false]]
        );
        $transcription = (new ResponseTranscriptor(null, null))->transcribe($psr7Response, $mockResponse);
    }

    public function testTranscribeHeaders(): void
    {
        $psr7Response  = new Response(202, ['X-PSR-7-default' => 'guzzle constructor']);
        $psr7Response  = $psr7Response->withAddedHeader('x-test', 'test 1');
        $psr7Response  = $psr7Response->withAddedHeader('x-test', 'test 2');
        $mockResponse  = $this->createSfWebResponse();
        $transcription = (new ResponseTranscriptor(null, null))->transcribe($psr7Response, $mockResponse);
        static::assertSame(
            [
                'X-Preset-Header' => 'sfWebResponse',
                'X-Psr-7-Default' => 'guzzle constructor',
                'X-Test'          => 'test 1, test 2',
            ],
            $transcription->getHttpHeaders()
        );
    }

    public function testTranscribeKeepSymfonyCookies(): void
    {
        $psr7Response = new Response(203);
        $mockResponse = $this->createSfWebResponse(
            503,
            null,
            ['X-Preset-Header' => 'Set sfWebResponse'],
            ['sf-default'      => ['name' => 'sf-default', 'value' => 'preset', 'expire' => null, 'path' => '/', 'domain' => '*', 'secure' => false, 'httpOnly' => false]]
        );
        $transcription = (new ResponseTranscriptor(null, null))->transcribe($psr7Response, $mockResponse);
        static::assertSame(
            [
                'sf-default' => ['name' => 'sf-default', 'value' => 'preset', 'expire' => null, 'path' => '/', 'domain' => '*', 'secure' => false, 'httpOnly' => false],
            ],
            $transcription->getCookies()
        );
    }

    public function testTranscribeStatusReasonVersion(): void
    {
        $psr7Response  = new Response(201, [], null, '1.1', 'Tested');
        $mockResponse  = $this->createSfWebResponse();
        $transcription = (new ResponseTranscriptor(null, null))->transcribe($psr7Response, $mockResponse);
        static::assertSame($mockResponse, $transcription);

        static::assertSame(201, $transcription->getStatusCode());
        static::assertSame('Tested', $transcription->getStatusText());
        static::assertSame('', $transcription->getContent());
        static::assertFalse($transcription->isHeaderOnly());
        static::assertSame([], $transcription->getCookies());
        static::assertSame(['X-Preset-Header' => 'sfWebResponse'], $transcription->getHttpHeaders());
        static::assertSame(
            [
                'http_protocol'     => 'HTTP/1.1',
                'charset'           => 'utf-8',
                'content_type'      => 'text/html',
                'send_http_headers' => true,
            ],
            $transcription->getOptions()
        );
    }

    public function testTranscribeBody(): void
    {
        $psr7Response  = new Response(200, [], 'PSR-7 Body');
        $mockResponse  = $this->createSfWebResponse();
        $transcription = (new ResponseTranscriptor(null, null))->transcribe($psr7Response, $mockResponse);
        static::assertSame('PSR-7 Body', $transcription->getContent());
    }

    public function testTranscribeOverwriteBody(): void
    {
        $psr7Response = new Response(200, [], 'PSR-7 Body');
        $mockResponse = $this->createSfWebResponse();
        $mockResponse->setContent('Symfony Content');
        $transcription = (new ResponseTranscriptor(null, null))->transcribe($psr7Response, $mockResponse);
        static::assertSame('PSR-7 Body', $transcription->getContent());
    }

    /**
     * @param int                                                                                                                           $code
     * @param null|string                                                                                                                   $reasonPhrase
     * @param string[]                                                                                                                      $headers
     * @param array<string, array{name:string, value:string, expire:null|string, path:string, domain: string, secure: bool, httpOnly:bool}> $cookies
     * @param array<int|string, mixed>                                                                                                      $options
     */
    private function createSfWebResponse(
        $code = 501,
        $reasonPhrase = 'Default Symfony reason! [OK]',
        $headers = ['X-Preset-Header' => 'sfWebResponse'],
        $cookies = [],
        array $options = ['http_protocol' => 'HTTP/1.0', 'charset' => 'utf-8', 'content_type' => 'text/html', 'send_http_headers' => true]
    ): \sfWebResponse {
        $symfonyResponseMock = new \sfWebResponse(null, $options);
        $symfonyResponseMock->prepare($code, $reasonPhrase, $headers, $cookies);

        return $symfonyResponseMock;
    }
}
