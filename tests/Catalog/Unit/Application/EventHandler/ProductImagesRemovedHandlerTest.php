<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\EventHandler;

use App\Catalog\Application\EventHandler\ProductImagesRemovedHandler;
use App\Catalog\Application\Service\Product\ProductMediaManagerInterface;
use App\Catalog\Domain\Event\ProductImagesRemovedDomainEvent;
use App\Shared\Domain\Exception\Services\Storage\FileStorageException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProductImagesRemovedHandlerTest extends TestCase
{
    private ProductMediaManagerInterface&MockObject $productMediaManager;

    protected function setUp(): void
    {
        $this->productMediaManager = $this->createMock(ProductMediaManagerInterface::class);
    }

    public function testItHandleEventCorrectly(): void
    {
        $stringPaths = self::getValidPaths();
        $event = new ProductImagesRemovedDomainEvent($stringPaths);

        $this->productMediaManager->expects(self::once())
            ->method('deleteProductImages')
            ->with(self::equalTo($this->mapPaths($stringPaths)));

        $this->createHandler()($event);
    }

    public function testThrowsExceptionWhenManagerThrowsException(): void
    {
        $stringPaths = self::getValidPaths();
        $event = new ProductImagesRemovedDomainEvent($stringPaths);

        $this->productMediaManager->expects(self::once())
            ->method('deleteProductImages')
            ->with(self::equalTo($this->mapPaths($stringPaths)))
            ->willThrowException(new FileStorageException());

        $this->expectException(FileStorageException::class);

        $this->createHandler()($event);
    }

    public function testThrowsExceptionWhenEventHasInvalidPaths(): void
    {
        $invalidPaths = ['/invalid/path/to/file.jpg'];
        $event = new ProductImagesRemovedDomainEvent($invalidPaths);

        $this->expectException(InvalidRelativePathException::class);

        $this->createHandler()($event);
    }

    private function createHandler(): ProductImagesRemovedHandler
    {
        return new ProductImagesRemovedHandler(
            productMediaManager: $this->productMediaManager
        );
    }

    /**
     * @return RelativeFilePath[]
     */
    private function mapPaths(array $paths): array
    {
        return array_map(
            static fn (string $path) => RelativeFilePath::fromString($path),
            $paths
        );
    }

    /**
     * @return string[]
     */
    private static function getValidPaths(): array
    {
        return [
            'products/2022/02/24/ulid1/ulid1.jpg',
            'products/2022/02/24/ulid2/ulid2.jpg',
            'products/2022/02/24/ulid3/ulid3.jpg',
        ];
    }
}
