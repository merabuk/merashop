<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product;

use App\Catalog\Application\Service\Product\ProductMediaManager;
use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\Service\CatalogStorageInterface;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid as ProductImageUlid;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Catalog\Support\ProductImageMother;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\TemporaryImageMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

final class ProductMediaManagerTest extends BaseUnitTest
{
    private TemporaryImageReadRepositoryInterface&MockObject $temporaryImageReadRepository;
    private TemporaryImageWriteRepositoryInterface&MockObject $temporaryImageWriteRepository;
    private CatalogStorageInterface&MockObject $catalogStorage;

    protected function setUp(): void
    {
        $this->temporaryImageReadRepository = $this->createMock(TemporaryImageReadRepositoryInterface::class);
        $this->temporaryImageWriteRepository = $this->createMock(TemporaryImageWriteRepositoryInterface::class);
        $this->catalogStorage = $this->createMock(CatalogStorageInterface::class);
    }

    #[DataProvider('ulidsProvider')]
    public function testItMapsTemporaryImageUlids(
        array $ulids,
        ?ImageCollection $productImages,
        int $expectedCount,
    ): void {
        $result = $this->createManager()->mapTemporaryImagesUlids($ulids, $productImages);

        self::assertCount($expectedCount, $result);
        foreach ($result as $i => $ulid) {
            self::assertInstanceOf(TemporaryImageUlid::class, $ulid);
            self::assertSame($ulids[$i], $ulid->value());
        }
    }

    public static function ulidsProvider(): iterable
    {
        $image = ProductImageMother::createWithData(isMain: true);
        $images = ImageCollection::fromArray([$image]);
        $tempUlids = self::getValidUlids();
        $count = count($tempUlids);

        yield 'without product images' => [
            'ulids' => $tempUlids,
            'productImages' => null,
            'expectedCount' => $count,
        ];
        yield 'with product images' => [
            'ulids' => [...$tempUlids, $image->getUlid()->value()],
            'productImages' => $images,
            'expectedCount' => $count,
        ];
    }

    public function testItMapsProductImagesUlidsForDelete(): void
    {
        $ulids = self::getValidUlids();
        $image = ProductImageMother::createWithData(isMain: true);
        $images = ImageCollection::fromArray([$image]);

        $result = $this->createManager()->mapProductImagesUlidsForDelete($ulids, $images);

        self::assertCount(1, $result);
        foreach ($result as $ulid) {
            self::assertInstanceOf(ProductImageUlid::class, $ulid);
            $removed = $images->getByUlid($ulid);
            self::assertNotNull($removed);
            self::assertTrue($removed->getUlid()->equals($ulid));
        }
    }

    public function testItActivatesImagesForProduct(): void
    {
        $ulids = self::getValidUlids();
        $product = ProductMother::createWithData(images: []);

        $temporaryImages = [];
        foreach ($ulids as $i => $ulid) {
            $temporaryImages[] = TemporaryImageMother::createWithData(
                ulid: $ulid,
                path: "temp/2024/03/19/img_{$i}.jpg",
                context: ContextEnum::ProductMain,
            );
        }

        $count = count($ulids);

        $this->expectsTemporaryImagesFound($ulids, $temporaryImages);
        $this->expectsGeneratesStoragePaths($count);
        $this->expectsStorageMoveImages($count);

        $this->createManager()->activateImagesForProduct($product, $ulids);

        self::assertCount($count, $product->getImages());
        foreach ($product->getImages() as $i => $image) {
            if (0 === $i) {
                self::assertTrue($image->isMain()->isTrue(), 'First image should be main');
            } else {
                self::assertTrue($image->isMain()->isFalse());
            }
            self::assertStringStartsWith('products/', $image->getPath()->value());
            self::assertSame($i, $image->getSortOrder()->value());
        }
    }

    public function testItSyncImagesForProduct(): void
    {
        $product = ProductMother::createWithData();
        $temporaryImageUlids = self::getValidUlids();
        $ulids = [];
        $expectedRemovedPaths = [];

        foreach ($product->getImages() as $i => $image) {
            if (0 === $i) {
                $expectedRemovedPaths[] = $image->getPath();

                continue;
            }
            $ulids[] = $image->getUlid()->value();
        }

        $ulids = [...$ulids, ...$temporaryImageUlids];

        $temporaryImages = [];
        foreach ($temporaryImageUlids as $i => $ulid) {
            $temporaryImages[] = TemporaryImageMother::createWithData(
                ulid: $ulid,
                path: "temp/2024/03/19/img_{$i}.jpg",
                context: ContextEnum::ProductMain,
            );
        }

        $temporaryImagesCount = count($temporaryImages);

        $this->expectsTemporaryImagesFound($temporaryImageUlids, $temporaryImages);
        $this->expectsGeneratesStoragePaths($temporaryImagesCount);
        $this->expectsStorageMoveImages($temporaryImagesCount);

        $removedPaths = $this->createManager()->syncProductImages($product, $ulids);

        foreach ($removedPaths as $i => $removedPath) {
            self::assertNotNull($expectedRemovedPaths[$i]);
            self::assertTrue($expectedRemovedPaths[$i]->equals($removedPath));
        }
        self::assertCount(count($ulids), $product->getImages());
        foreach ($product->getImages() as $i => $image) {
            if (0 === $i) {
                self::assertTrue($image->isMain()->isTrue(), 'First image should be main');
            } else {
                self::assertTrue($image->isMain()->isFalse());
            }
            self::assertStringStartsWith('products/', $image->getPath()->value());
            self::assertSame($i, $image->getSortOrder()->value());
        }
    }

    public function testItDeletesTemporaryImages(): void
    {
        $ulids = self::getMappedValidUlids();

        $this->temporaryImageWriteRepository->expects(self::once())
            ->method('deleteByUlids')
            ->with(self::equalTo($ulids))
            ->willReturn(count($ulids));

        $this->createManager()->deleteTemporaryImages($ulids);
    }

    public function testItDeletesProductImages(): void
    {
        $paths = [];
        foreach (self::getValidUlids() as $ulid) {
            $paths[] = RelativeFilePath::fromString("products/{$ulid}.jpg");
        }

        $this->catalogStorage->expects(self::exactly(count($paths)))
            ->method('delete')
            ->with(self::logicalOr(
                ...array_map(fn (RelativeFilePath $path) => self::equalTo($path), $paths)
            ));

        $this->createManager()->deleteProductImages($paths);
    }

    private function createManager(): ProductMediaManager
    {
        return new ProductMediaManager(
            temporaryImageReadRepository: $this->temporaryImageReadRepository,
            temporaryImageWriteRepository: $this->temporaryImageWriteRepository,
            storage: $this->catalogStorage
        );
    }

    /**
     * @param ?string[] $ulids
     *
     * @return TemporaryImageUlid[]
     */
    private static function getMappedValidUlids(?array $ulids = null): array
    {
        return array_map(fn (string $ulid) => TemporaryImageUlid::fromString($ulid), $ulids ?? self::getValidUlids());
    }

    /**
     * @return string[]
     */
    private static function getValidUlids(): array
    {
        return [
            '01KKTVY7D6D7S1BCSBB3GQA8B6',
            '01KKTVY7D6D7S1BCSBB3GQA8B7',
            '01KKTVY7D6D7S1BCSBB3GQA8B8',
        ];
    }

    /**
     * @param string[]         $ulids
     * @param TemporaryImage[] $temporaryImages
     */
    private function expectsTemporaryImagesFound(array $ulids, array $temporaryImages): void
    {
        $this->temporaryImageReadRepository->expects(self::once())
            ->method('findByUlids')
            ->with(self::getMappedValidUlids($ulids))
            ->willReturn($temporaryImages);
    }

    private function expectsGeneratesStoragePaths(int $times): void
    {
        $this->catalogStorage->expects(self::exactly($times))
            ->method('generateProductImageStoragePath')
            ->willReturnCallback(function (RelativeFilePath $path) {
                $newPathString = str_replace('temp/', 'products/', $path->value());

                return RelativeFilePath::fromString($newPathString);
            });
    }

    private function expectsStorageMoveImages(int $times): void
    {
        $this->catalogStorage->expects(self::exactly($times))
            ->method('move')
            ->with(
                self::stringStartsWith('temp/'),
                self::stringStartsWith('products/')
            );
    }
}
