<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Service;

use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Catalog\Infrastructure\Service\CatalogFlysystemStorage;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Catalog\Support\TemporaryImageMother;
use App\Tests\Shared\Support\Traits\VfsStreamTrait;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

final class CatalogFlysystemStorageTest extends TestCase
{
    use VfsStreamTrait;

    private FilesystemOperator $filesystem;

    public function setUp(): void
    {
        $this->setupVfs('catalog_uploads');
        $this->filesystem = $this->createMock(FilesystemOperator::class);
    }

    public function testGetPublicUrl(): void
    {
        $path = RelativeFilePath::fromString('/path/to/file.jpg');

        $result = $this->createStorage()->getPublicUrl($path);

        self::assertSame($path->value(), $result);
    }

    public function testGenerateTemporaryImageStoragePath(): void
    {
        $clock = new MockClock('2025-07-29 00:00:00');
        $ulid = Ulid::fromString(TemporaryImageMother::DEFAULT_ULID);
        $extension = 'jpg';
        $fileName = sprintf('product.%s', $extension);
        $rawFile = RawFile::fromPath(
            localPath: $this->createVirtualFile(name: $fileName, content: 'binary_content'),
            originalName: $fileName,
            extension: $extension,
            mimeType: 'image/jpeg'
        );

        $result = $this->createStorage($clock)->generateTemporaryImageStoragePath($ulid, $rawFile);

        $expectedPath = implode(RelativeFilePath::SEPARATOR, [
            'temp',
            $clock->now()->format('Y'),
            $clock->now()->format('m'),
            $clock->now()->format('d'),
            $ulid->value(),
            $ulid->value().'.'.$extension,
        ]);

        self::assertSame($expectedPath, $result->value());
    }

    private function createStorage(?ClockInterface $clock = null): CatalogFlysystemStorage
    {
        $clock ??= new MockClock();

        return new CatalogFlysystemStorage(
            clock: $clock,
            filesystem: $this->filesystem
        );
    }
}
