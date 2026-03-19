<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\UploadTemporaryImage;

use App\Catalog\Application\Command\UploadTemporaryImage\UploadTemporaryImageCommand;
use App\Catalog\Application\Command\UploadTemporaryImage\UploadTemporaryImageHandler;
use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\Service\CatalogStorageInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\ValueObject\File\RawFile;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Catalog\Support\TemporaryImageMother;
use App\Tests\Shared\Support\Traits\UlidGenerationTrait;
use App\Tests\Shared\Support\Traits\VfsStreamTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UploadTemporaryImageHandlerTest extends TestCase
{
    use VfsStreamTrait;
    use UlidGenerationTrait;

    private CatalogStorageInterface&MockObject $catalogStorage;
    private TemporaryImageWriteRepositoryInterface&MockObject $writeRepository;

    public function setUp(): void
    {
        $this->setupVfs('catalog_uploads');
        $this->setUlidGenerator();
        $this->catalogStorage = $this->createMock(CatalogStorageInterface::class);
        $this->writeRepository = $this->createMock(TemporaryImageWriteRepositoryInterface::class);
    }

    public function testItHandleSuccess(): void
    {
        $fileName = 'product.jpg';
        $localPath = $this->createVirtualFile(name: $fileName, content: 'binary_content');

        $file = RawFile::fromPath(
            localPath: $localPath,
            originalName: $fileName,
            extension: 'jpg',
            mimeType: 'image/jpeg'
        );

        $expectedUlid = TemporaryImageMother::DEFAULT_ULID;
        $expectedStoragePath = sprintf('temp/expected/storage/path/%s.%s', $expectedUlid, $file->getExtension());
        $exceptedContext = ContextEnum::ProductMain;
        $command = new UploadTemporaryImageCommand(file: $file, context: $exceptedContext);

        $this->expectGenerateUlid($expectedUlid);

        $this->catalogStorage->expects(self::once())
            ->method('generateTemporaryImageStoragePath')
            ->with(
                self::callback(fn (Ulid $ulid) => $ulid->value() === $expectedUlid),
                self::equalTo($file)
            )
            ->willReturn(RelativeFilePath::fromString($expectedStoragePath));

        $this->catalogStorage->expects(self::once())
            ->method('uploadFromLocalPath')
            ->with(
                self::equalTo($file->getLocalPath()),
                self::equalTo($expectedStoragePath)
            );

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (TemporaryImage $image) use (
                $expectedUlid,
                $expectedStoragePath,
                $exceptedContext
            ): bool {
                $expectedUlidCorrect = $expectedUlid === $image->getUlid()->value();
                $expectedPathCorrect = $expectedStoragePath === $image->getPath()->value();
                $expectedContextCorrect = $exceptedContext === $image->getContext()->value();

                return $expectedUlidCorrect && $expectedPathCorrect && $expectedContextCorrect;
            }))
            ->willReturnArgument(0);

        $result = $this->createHandler()($command);

        self::assertSame($expectedUlid, $result);
    }

    private function createHandler(): UploadTemporaryImageHandler
    {
        return new UploadTemporaryImageHandler(
            ulidGenerator: $this->ulidGenerator,
            catalogStorage: $this->catalogStorage,
            writeRepository: $this->writeRepository
        );
    }
}
