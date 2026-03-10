<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\File;

use App\Shared\Domain\Exception\ValueObject\InvalidRawFileException;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Tests\Shared\Support\Traits\VfsStreamTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RawFileTest extends TestCase
{
    use VfsStreamTrait;

    protected function setUp(): void
    {
        $this->setUpVfs('raw_file_test');
    }

    #[DataProvider('validRawFileDataProvider')]
    public function testItCreatesRawFile(
        string $fileName,
        string $fileContent,
        string $extension,
        string $mimeType,
    ): void {
        $localPath = $this->createVirtualFile(name: $fileName, content: $fileContent);

        $vo = RawFile::fromPath(
            localPath: $localPath,
            originalName: $fileName,
            extension: $extension,
            mimeType: $mimeType,
        );

        self::assertSame($localPath, $vo->getLocalPath());
        self::assertSame($fileName, $vo->getOriginalName());
        self::assertSame($extension, $vo->getExtension());
        self::assertSame($mimeType, $vo->getMimeType());
    }

    public static function validRawFileDataProvider(): iterable
    {
        yield 'txt file' => [
            'fileName' => 'test.txt',
            'fileContent' => 'Hello, world!',
            'extension' => 'txt',
            'mimeType' => 'text/plain',
        ];
        yield 'jpg file' => [
            'fileName' => 'test.jpg',
            'fileContent' => 'binary_content',
            'extension' => 'jpg',
            'mimeType' => 'image/jpeg',
        ];
    }

    public function testItUsesDefaultExtensionIfNoneProvided(): void
    {
        $localPath = $this->createVirtualFile(name: 'test.txt', content: 'Hello, world!');

        $vo = RawFile::fromPath(
            localPath: $localPath,
            originalName: 'test',
            extension: null,
            mimeType: 'text/plain',
        );

        self::assertSame(RawFile::DEFAULT_EXTENSION, $vo->getExtension());
    }

    public function testItUsesDefaultMimeTypeIfNoneProvided(): void
    {
        $localPath = $this->createVirtualFile(name: 'test.txt', content: 'Hello, world!');

        $vo = RawFile::fromPath(
            localPath: $localPath,
            originalName: 'test',
            extension: 'txt',
            mimeType: null,
        );

        self::assertSame(RawFile::DEFAULT_MIME_TYPE, $vo->getMimeType());
    }

    #[DataProvider('nonExistentFileProvider')]
    public function testThrowsExceptionOnNonExistentFile(string $localPath): void
    {
        $this->expectException(InvalidRawFileException::class);

        RawFile::fromPath(localPath: $localPath, originalName: 'invalid.file');
    }

    public static function nonExistentFileProvider(): iterable
    {
        yield 'absolute path' => ['/tmp/non_existent_file_12345.txt'];
        yield 'relative path' => ['relative/path/to/nothing.jpg'];
    }

    public function testThrowsExceptionWhenFileIsNotReadable(): void
    {
        $localPath = $this->createVirtualFile(
            name: 'secret.txt',
            content: 'content',
            permissions: 0000
        );

        $this->expectException(InvalidRawFileException::class);

        RawFile::fromPath(localPath: $localPath, originalName: 'secret.txt');
    }
}
