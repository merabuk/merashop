<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Service\Storage;

use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Shared\Infrastructure\Service\Storage\FlysystemStorage;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Support\Traits\VfsStreamTrait;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;

final class FlysystemStorageTest extends BaseUnitTest
{
    use VfsStreamTrait;

    private FilesystemOperator&MockObject $flysystem;
    private FlysystemStorage $storage;

    protected function setUp(): void
    {
        $this->setupVfs('storage_test');
        $this->flysystem = $this->createMock(FilesystemOperator::class);

        $this->storage = $this->createAnonymousClassForFlysystemStorage();
    }

    public function testUploadFromLocalPathSuccessfully(): void
    {
        $fileName = 'source.jpg';
        $targetPath = 'dest/file.jpg';
        $localPath = $this->createVirtualFile($fileName, 'content');

        $capturedResource = null;

        $this->flysystem->expects(self::once())
            ->method('writeStream')
            ->with(
                self::equalTo($targetPath),
                self::callback(function ($resource) use (&$capturedResource) {
                    $capturedResource = $resource;

                    return is_resource($resource);
                })
            );

        $this->storage->uploadFromLocalPath($localPath, $targetPath);

        self::assertFalse(is_resource($capturedResource), 'The file stream must be closed after upload.');
    }

    public function testUploadFromLocalPathThrowsExceptionOnMissingFile(): void
    {
        $invalidPath = $this->getVfsBaseUrl().'/non-existent.jpg';

        $this->expectException(FileStorageException::class);
        $this->expectExceptionMessage('Fail writing stream');

        $this->storage->uploadFromLocalPath($invalidPath, 'anywhere/test.jpg');
    }

    public function testUploadMethodDelegatesToFlysystem(): void
    {
        $path = 'test.txt';
        $content = 'plain text';

        $this->flysystem->expects(self::once())
            ->method('write')
            ->with($path, $content);

        $this->storage->upload($path, $content);
    }

    public function testDeleteMethodDelegatesToFlysystem(): void
    {
        $pathVo = RelativeFilePath::fromString('path/to/delete.jpg');

        $this->flysystem->expects(self::once())
            ->method('delete')
            ->with($pathVo->value());

        $this->storage->delete($pathVo);
    }

    private function createAnonymousClassForFlysystemStorage(): FlysystemStorage
    {
        return new readonly class($this->flysystem) extends FlysystemStorage {
            public function getPublicUrl(RelativeFilePath $path): string
            {
                return 'storage_test_url_stub';
            }
        };
    }
}
