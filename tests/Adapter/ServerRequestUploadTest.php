<?php

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

class ServerRequestUploadTest extends TestCase
{
    public function testSingleUploadedFiles(): void
    {
        $request = $this->createRequest(
            'POST',
            [],
            null,
            null,
            [
                'file-one' => [
                    'name'     => 'that.jpg',
                    'type'     => 'image/jpeg',
                    'tmp_name' => '/tmp/php1337',
                    'error'    => 0,
                    'size'     => 1337,
                ],
            ]
        );

        $files = $request->getUploadedFiles();
        $this->assertCount(1, $files);
        $file = $files['file-one'];
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertSame(1337, $file->getSize());
        $this->assertSame('that.jpg', $file->getClientFilename());
    }

    public function testSingleFormUploadedFiles(): void
    {
        $request = $this->createRequest(
            'POST',
            [],
            null,
            null,
            [
                'form1' => [
                    'file-one' => [
                        'name'     => 'that.jpg',
                        'type'     => 'image/jpeg',
                        'tmp_name' => '/tmp/php1337',
                        'error'    => 0,
                        'size'     => 1337,
                    ],
                ],
            ]
        );

        $files = $request->getUploadedFiles();
        $this->assertCount(1, $files);
        $file = $files['form1']['file-one'];
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertSame(1337, $file->getSize());
        $this->assertSame('that.jpg', $file->getClientFilename());
    }

    /**
     * @param null|array<array|array<array>> $files
     *
     * @throws \InvalidArgumentException
     * @return Request
     */
    private function createRequest(
        string $method = '',
        array $adapterOptions = [],
        ?string $content = null,
        ?string $uri = null,
        ?array $files = []
    ): Request {
        $symfonyRequestMock = new \sfWebRequest();
        $symfonyRequestMock->prepare($method, [], [], [], [], [], $content, $uri, $files);

        return Request::fromSfWebRequest($symfonyRequestMock, $adapterOptions);
    }
}
