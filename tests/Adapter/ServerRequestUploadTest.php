<?php

declare(strict_types=1);

namespace brnc\Tests\Symfony1\Message\Adapter;

use brnc\Symfony1\Message\Adapter\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @internal
 *
 * @coversNothing
 */
final class ServerRequestUploadTest extends TestCase
{
    public function testSingleUploadedFile(): void
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
        static::assertCount(1, $files);
        $file = $files['file-one'];
        static::assertInstanceOf(UploadedFileInterface::class, $file);
        static::assertSame(1337, $file->getSize());
        static::assertSame('that.jpg', $file->getClientFilename());
    }

    public function testFormSingleUploadedFile(): void
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
        static::assertCount(1, $files);
        $file = $files['form1']['file-one'];
        static::assertInstanceOf(UploadedFileInterface::class, $file);
        static::assertSame(1337, $file->getSize());
        static::assertSame('that.jpg', $file->getClientFilename());
    }

    public function testFormMultipleUploadedFile(): void
    {
        $request = $this->createRequest(
            'POST',
            [],
            null,
            null,
            [
                'mass_upload' => [
                    'foo' => [
                        'error'    => 0,
                        'name'     => 'test.csv',
                        'type'     => 'text/csv',
                        'tmp_name' => '/tmp/phpmYRXeG',
                        'size'     => 23,
                    ],
                    'bar' => [
                        'error'    => 0,
                        'name'     => 'bar.sql',
                        'type'     => 'application/sql',
                        'tmp_name' => '/tmp/phpYCTUy1',
                        'size'     => 7206,
                    ],
                    'baz' => [
                        'error'    => 0,
                        'name'     => 'Notes.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => '/tmp/phpnhBSSm',
                        'size'     => 17962,
                    ],
                ],
            ]
        );

        $files = $request->getUploadedFiles()['mass_upload'];
        static::assertCount(3, $files);
        static::assertInstanceOf(UploadedFileInterface::class, $files['foo']);
        static::assertInstanceOf(UploadedFileInterface::class, $files['bar']);
        static::assertInstanceOf(UploadedFileInterface::class, $files['baz']);
    }

    public function testDeeplyNestledUploadedFile(): void
    {
        $request = $this->createRequest(
            'POST',
            [],
            null,
            null,
            [
                'form' => [
                    'foo' => [
                        'bar' => [
                            'baz' => [
                                'that-file' => [
                                    'name'     => 'that.jpg',
                                    'type'     => 'image/jpeg',
                                    'tmp_name' => '/tmp/php1337',
                                    'error'    => 0,
                                    'size'     => 1337,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $files = $request->getUploadedFiles();
        static::assertCount(1, $files);
        $file = $files['form']['foo']['bar']['baz']['that-file'];
        static::assertInstanceOf(UploadedFileInterface::class, $file);
        static::assertSame(1337, $file->getSize());
        static::assertSame('that.jpg', $file->getClientFilename());
    }

    public function testMultiFormUploadedFiles(): void
    {
        $request = $this->createRequest(
            'POST',
            [],
            null,
            null,
            [
                'file-foo' => [
                    'name'     => 'test.csv',
                    'type'     => 'text/csv',
                    'tmp_name' => '/tmp/php23',
                    'error'    => 0,
                    'size'     => 23,
                ],
                'form-bar' => [
                    'foo'      => [
                        'bar' => [
                            'baz' => [
                                'file-bar' => [
                                    'name'     => 'that.jpg',
                                    'type'     => 'image/jpeg',
                                    'tmp_name' => '/tmp/php1337',
                                    'error'    => 0,
                                    'size'     => 1337,
                                ],
                            ],
                        ],
                    ],
                    'file-baz' => [
                        'error'    => 0,
                        'name'     => 'text.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => '/tmp/php42',
                        'size'     => 42,
                    ],
                ],
            ]
        );

        $files = $request->getUploadedFiles();
        static::assertCount(2, $files);

        $file1 = $files['file-foo'];
        static::assertInstanceOf(UploadedFileInterface::class, $file1);
        static::assertSame(23, $file1->getSize());
        static::assertSame('test.csv', $file1->getClientFilename());

        $file2 = $files['form-bar']['foo']['bar']['baz']['file-bar'];
        static::assertInstanceOf(UploadedFileInterface::class, $file2);
        static::assertSame(1337, $file2->getSize());
        static::assertSame('that.jpg', $file2->getClientFilename());

        $file3 = $files['form-bar']['file-baz'];
        static::assertInstanceOf(UploadedFileInterface::class, $file3);
        static::assertSame(42, $file3->getSize());
        static::assertSame('text.txt', $file3->getClientFilename());
    }

    /**
     * @param null|array<array|array<array>> $files
     *
     * @throws \InvalidArgumentException
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
