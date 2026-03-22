<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product;

use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\Service\Product\ProductApplicationFactory;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\ProductPriceMother;
use App\Tests\Catalog\Support\Traits\ProductHelperTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\TestCase;

final class ProductApplicationFactoryTest extends TestCase
{
    use ProductHelperTrait;
    use ValueObjectAssertionTrait;

    public function testItMapsCategoryIds(): void
    {
        $ids = [1, 2, 3];

        $result = $this->createFactory()->mapCategoryIds($ids);

        self::assertCount(count($ids), $result);
        foreach ($result as $i => $id) {
            self::assertInstanceOf(CategoryId::class, $id);
            self::assertSame($ids[$i], $id->value());
        }
    }

    public function testItMapsAttributeIds(): void
    {
        $ids = [1, 2, 3];
        $attributeValues = [];
        foreach ($ids as $id) {
            $attributeValues[] = new ProductAttributeValueData(
                attributeId: $id,
                value: 'value '.$id,
            );
        }

        $result = $this->createFactory()->mapAttributeIds($attributeValues);

        self::assertCount(count($ids), $result);
        foreach ($result as $i => $id) {
            self::assertInstanceOf(AttributeId::class, $id);
            self::assertSame($ids[$i], $id->value());
        }
    }

    public function testItCreatesFromCommand(): void
    {
        $product = ProductMother::createWithData();
        $command = $this->fillAndGetCreateCommand($product);

        $created = $this->createFactory()->createFromCommand($command, $product->getUlid()->value());

        self::assertTrue($product->getUlid()->equals($created->getUlid()));
        self::assertTrue($product->getSku()->equals($created->getSku()));
        self::assertTrue($product->getStatus()->equals($created->getStatus()));
        self::assertTrue($product->getTranslations()->equals($created->getTranslations()));
        self::assertTrue($product->getVersion()->equals($created->getVersion()));
        self::assertTrue($product->getCreatedBy()->equals($created->getCreatedBy()));
        self::assertTrue($product->getPrices()->equals($created->getPrices()));
        self::assertTrue($product->getCategoryIds()->equals($created->getCategoryIds()));
        self::assertTrue($product->getAttributeValues()->equals($created->getAttributeValues()));
        self::assertCount(0, $created->getImages());
        $this->assertVoEqualsOrNull($product->getUpdatedBy(), $created->getUpdatedBy());
    }

    public function testItUpdatesFromCommand(): void
    {
        $product = ProductMother::createWithData(
            ulid: ProductMother::DEFAULT_ULID,
            sku: 'OLD-SKU-L',
            status: StatusEnum::Draft,
            translations: [
                'en' => ['name' => 'Old Product'],
            ],
            version: 2,
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
            prices: [ProductPriceMother::createWithData()],
            categoryIds: [],
            attributeValues: [],
            images: [],
            id: 123
        );
        $productForUpdate = ProductMother::createWithData(
            ulid: '01KM2VMRTD4XN3WA67FPMKAWDE',
            version: 1,
            createdByUlid: '01KM2VXDT53FFTM8A520WFDK9T',
            updatedByUlid: ProductMother::DEFAULT_ADMIN_ULID,
            id: 456
        );
        $command = $this->fillAndGetUpdateCommand($productForUpdate);

        $this->createFactory()->updateFromCommand($product, $command);

        self::assertFalse($product->getId()->equals($productForUpdate->getId()), "Id mustn't changed");
        self::assertFalse($product->getUlid()->equals($productForUpdate->getUlid()), "Ulid mustn't changed");
        self::assertTrue($product->getSku()->equals($productForUpdate->getSku()));
        self::assertTrue($product->getStatus()->equals($productForUpdate->getStatus()));
        self::assertTrue($product->getTranslations()->equals($productForUpdate->getTranslations()));
        self::assertFalse($product->getVersion()->equals($productForUpdate->getVersion()), "Version mustn't changed");
        self::assertFalse($product->getCreatedBy()->equals($productForUpdate->getCreatedBy()), "Created by mustn't changed");
        self::assertTrue($product->getPrices()->equals($productForUpdate->getPrices()));
        self::assertTrue($product->getCategoryIds()->equals($productForUpdate->getCategoryIds()));
        self::assertTrue($product->getAttributeValues()->equals($productForUpdate->getAttributeValues()));
        self::assertCount(0, $product->getImages());
        self::assertTrue($product->getUpdatedBy()->equals($productForUpdate->getUpdatedBy()));
    }

    private function createFactory(): ProductApplicationFactory
    {
        return new ProductApplicationFactory();
    }
}
