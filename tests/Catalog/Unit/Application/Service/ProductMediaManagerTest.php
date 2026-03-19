<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service;

use App\Catalog\Application\Service\ProductMediaManager;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\Service\CatalogStorageInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid as TemporaryImageUlid;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\TemporaryImageMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProductMediaManagerTest extends TestCase
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

    public function testItMapsTemporaryImageUlids(): void
    {
        $ulids = self::getValidUlids();

        $result = $this->createManager()->mapTemporaryImagesUlids($ulids);

        self::assertCount(count($ulids), $result);
        foreach ($result as $i => $ulid) {
            self::assertInstanceOf(TemporaryImageUlid::class, $ulid);
            self::assertSame($ulids[$i], $ulid->value());
        }
    }

    public function testItActivatesImagesForProduct(): void
    {
        $ulids = self::getMappedValidUlids();
        $product = ProductMother::createWithData(images: []);

        $temporaryImages = [];
        foreach ($ulids as $i => $ulid) {
            $temporaryImages[] = TemporaryImageMother::createWithData(
                ulid: $ulid->value(),
                path: "temp/2024/03/19/img_{$i}.jpg"
            );
        }

        $this->temporaryImageReadRepository->expects(self::once())
            ->method('findByUlids')
            ->with($ulids)
            ->willReturn($temporaryImages);

        $this->catalogStorage->method('generateProductImageStoragePath')
            ->willReturnCallback(function (RelativeFilePath $path) {
                $newPathString = str_replace('temp/', 'products/', $path->value());

                return RelativeFilePath::fromString($newPathString);
            });

        $this->catalogStorage->expects(self::exactly(count($ulids)))
            ->method('move')
            ->with(
                self::stringStartsWith('temp/'),
                self::stringStartsWith('products/')
            );

        $this->createManager()->activateImagesForProduct($product, $ulids);

        $images = $product->getImages()->all();
        self::assertCount(count($ulids), $images);

        self::assertTrue($images[0]->isMain()->isTrue(), 'First image should be main');
        self::assertTrue($images[1]->isMain()->isFalse());

        self::assertStringStartsWith('products/', $images[0]->getPath()->value());
        self::assertSame(0, $images[0]->getSortOrder()->value());
        self::assertSame(1, $images[1]->getSortOrder()->value());
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

    private function createManager(): ProductMediaManager
    {
        return new ProductMediaManager(
            temporaryImageReadRepository: $this->temporaryImageReadRepository,
            temporaryImageWriteRepository: $this->temporaryImageWriteRepository,
            storage: $this->catalogStorage
        );
    }

    /**
     * @return TemporaryImageUlid[]
     */
    private static function getMappedValidUlids(): array
    {
        return array_map(fn (string $ulid) => TemporaryImageUlid::fromString($ulid), self::getValidUlids());
    }

    /**
     * @return string[]
     */
    private static function getValidUlids(): array
    {
        return [
            '01KKTVY7D6D7S1BCSBB3GQA8B3',
            '01KKTVY7D6D7S1BCSBB3GQA8B4',
            '01KKTVY7D6D7S1BCSBB3GQA8B5',
        ];
    }
}
