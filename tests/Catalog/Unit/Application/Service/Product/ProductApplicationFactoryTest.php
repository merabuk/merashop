<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\Service\Product\AttributeValue\ProductAttributeValueProviderInterface;
use App\Catalog\Application\Service\Product\ProductApplicationFactory;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum as PriceTypeEnum;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\ProductPriceMother;
use App\Tests\Catalog\Support\Traits\ProductHelperTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ProductApplicationFactoryTest extends TestCase
{
    use ProductHelperTrait;
    use ValueObjectAssertionTrait;

    private AttributeReadRepositoryInterface&MockObject $attributeReadRepository;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->attributeReadRepository = $this->createMock(AttributeReadRepositoryInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
    }

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

        $mockValue = $this->createMock(AttributeValueDataInterface::class);

        foreach ($ids as $id) {
            $attributeValues[] = new ProductAttributeValueData(
                attributeId: $id,
                value: $mockValue,
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
        $optionSelect1 = AttributeOptionMother::createWithData(
            ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
            code: 'select-1',
            id: 5201
        );
        $optionSelect2 = AttributeOptionMother::createWithData(
            code: 'select-2',
            id: 5202
        );
        $optionDimension1 = AttributeOptionMother::createWithData(
            ulid: '01KMJ1ANFES9VS1HYEYBDCNCFW',
            code: 'dimension-1',
            id: 5301
        );
        $optionDimension2 = AttributeOptionMother::createWithData(
            code: 'dimension-2',
            id: 5302
        );

        $attributeString = AttributeMother::createWithData(type: TypeEnum::String, id: 5100);
        $attributeSelect = AttributeMother::createWithData(
            type: TypeEnum::Select,
            options: [$optionSelect1, $optionSelect2],
            id: 5200
        );
        $attributeDimension = AttributeMother::createWithData(
            type: TypeEnum::Dimension,
            options: [$optionDimension1, $optionDimension2],
            id: 5300
        );
        $productStringAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: $attributeString->getId()->value(),
            attributeType: $attributeString->getType()->value(),
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
        );
        $productSelectAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: $attributeSelect->getId()->value(),
            attributeType: $attributeSelect->getType()->value(),
            optionId: $optionSelect1->getId()->value(),
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
        );
        $productDimensionAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: $attributeDimension->getId()->value(),
            attributeType: $attributeDimension->getType()->value(),
            optionId: $optionDimension1->getId()->value(),
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
        );

        $product = ProductMother::createWithData(attributeValues: [
            $productStringAttributeValue,
            $productSelectAttributeValue,
            $productDimensionAttributeValue,
        ]);
        $command = $this->fillAndGetCreateCommand($product);

        $this->attributeReadRepository->expects(self::once())
            ->method('findByIds')
            ->with(self::equalTo([
                $attributeString->getId(),
                $attributeSelect->getId(),
                $attributeDimension->getId(),
            ]))
            ->willReturn([$attributeString, $attributeSelect, $attributeDimension]);

        $this->container->expects(self::exactly(3))
            ->method('has')
            ->with(self::logicalOr(
                self::equalTo($attributeString->getType()->value()->value),
                self::equalTo($attributeSelect->getType()->value()->value),
                self::equalTo($attributeDimension->getType()->value()->value),
            ))
            ->willReturn(true);
        $this->container->expects(self::exactly(3))
            ->method('get')
            ->with(self::logicalOr(
                self::equalTo($attributeString->getType()->value()->value),
                self::equalTo($attributeSelect->getType()->value()->value),
                self::equalTo($attributeDimension->getType()->value()->value),
            ))
            ->willReturnCallback(function (string $type) use (
                $attributeString,
                $productStringAttributeValue,
                $attributeSelect,
                $productSelectAttributeValue,
                $attributeDimension,
                $productDimensionAttributeValue,
            ): ProductAttributeValueProviderInterface&MockObject {
                $provider = $this->createMock(ProductAttributeValueProviderInterface::class);

                match (true) {
                    $type === $attributeString->getType()->value()->value => $provider
                        ->expects(self::once())
                        ->method('handle')
                        ->willReturn([$productStringAttributeValue]),
                    $type === $attributeSelect->getType()->value()->value => $provider
                        ->expects(self::once())
                        ->method('handle')
                        ->willReturn([$productSelectAttributeValue]),
                    $type === $attributeDimension->getType()->value()->value => $provider
                        ->expects(self::once())
                        ->method('handle')
                        ->willReturn([$productDimensionAttributeValue]),
                    default => null,
                };

                return $provider;
            });

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
        $existingPrice = ProductPriceMother::createWithData(
            currency: CurrencyEnum::UAH,
            type: PriceTypeEnum::Regular,
        );
        $product = ProductMother::createWithData(
            ulid: ProductMother::DEFAULT_ULID,
            sku: 'OLD-SKU-L',
            status: StatusEnum::Draft,
            translations: [
                'en' => ['name' => 'Old Product'],
            ],
            version: 2,
            createdByUlid: '01KM2VXDT53FFTM8A520WFDK9T',
            prices: [$existingPrice],
            categoryIds: [],
            attributeValues: [],
            images: [],
            id: 123
        );
        $optionSelect1 = AttributeOptionMother::createWithData(
            ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
            code: 'select-1',
            id: 5201
        );
        $optionSelect2 = AttributeOptionMother::createWithData(
            code: 'select-2',
            id: 5202
        );
        $optionDimension1 = AttributeOptionMother::createWithData(
            ulid: '01KMJ1ANFES9VS1HYEYBDCNCFW',
            code: 'dimension-1',
            id: 5301
        );
        $optionDimension2 = AttributeOptionMother::createWithData(
            code: 'dimension-2',
            id: 5302
        );

        $attributeString = AttributeMother::createWithData(type: TypeEnum::String, id: 5100);
        $attributeSelect = AttributeMother::createWithData(
            type: TypeEnum::Select,
            options: [$optionSelect1, $optionSelect2],
            id: 5200
        );
        $attributeDimension = AttributeMother::createWithData(
            type: TypeEnum::Dimension,
            options: [$optionDimension1, $optionDimension2],
            id: 5300
        );
        $productStringAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: $attributeString->getId()->value(),
            attributeType: $attributeString->getType()->value(),
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
        );
        $productSelectAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: $attributeSelect->getId()->value(),
            attributeType: $attributeSelect->getType()->value(),
            optionId: $optionSelect1->getId()->value(),
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
        );
        $productDimensionAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: $attributeDimension->getId()->value(),
            attributeType: $attributeDimension->getType()->value(),
            optionId: $optionDimension1->getId()->value(),
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
        );
        $productForUpdate = ProductMother::createWithData(
            ulid: '01KM2VMRTD4XN3WA67FPMKAWDE',
            version: 1,
            createdByUlid: ProductMother::DEFAULT_ADMIN_ULID,
            updatedByUlid: ProductMother::DEFAULT_ADMIN_ULID,
            attributeValues: [
                $productStringAttributeValue,
                $productSelectAttributeValue,
                $productDimensionAttributeValue,
            ],
            id: 456
        );
        $command = $this->fillAndGetUpdateCommand($productForUpdate);

        $this->attributeReadRepository->expects(self::once())
            ->method('findByIds')
            ->with(self::equalTo([
                $attributeString->getId(),
                $attributeSelect->getId(),
                $attributeDimension->getId(),
            ]))
            ->willReturn([$attributeString, $attributeSelect, $attributeDimension]);

        $this->container->expects(self::exactly(3))
            ->method('has')
            ->with(self::logicalOr(
                self::equalTo($attributeString->getType()->value()->value),
                self::equalTo($attributeSelect->getType()->value()->value),
                self::equalTo($attributeDimension->getType()->value()->value),
            ))
            ->willReturn(true);
        $this->container->expects(self::exactly(3))
            ->method('get')
            ->with(self::logicalOr(
                self::equalTo($attributeString->getType()->value()->value),
                self::equalTo($attributeSelect->getType()->value()->value),
                self::equalTo($attributeDimension->getType()->value()->value),
            ))
            ->willReturnCallback(function (string $type) use (
                $attributeString,
                $productStringAttributeValue,
                $attributeSelect,
                $productSelectAttributeValue,
                $attributeDimension,
                $productDimensionAttributeValue,
            ): ProductAttributeValueProviderInterface&MockObject {
                $provider = $this->createMock(ProductAttributeValueProviderInterface::class);

                match (true) {
                    $type === $attributeString->getType()->value()->value => $provider
                        ->expects(self::once())
                        ->method('handle')
                        ->willReturn([$productStringAttributeValue]),
                    $type === $attributeSelect->getType()->value()->value => $provider
                        ->expects(self::once())
                        ->method('handle')
                        ->willReturn([$productSelectAttributeValue]),
                    $type === $attributeDimension->getType()->value()->value => $provider
                        ->expects(self::once())
                        ->method('handle')
                        ->willReturn([$productDimensionAttributeValue]),
                    default => null,
                };

                return $provider;
            });

        $this->createFactory()->updateFromCommand($product, $command);

        self::assertFalse($product->getId()->equals($productForUpdate->getId()), "Id mustn't changed");
        self::assertFalse($product->getUlid()->equals($productForUpdate->getUlid()), "Ulid mustn't changed");
        self::assertTrue($product->getSku()->equals($productForUpdate->getSku()));
        self::assertTrue($product->getStatus()->equals($productForUpdate->getStatus()));
        self::assertTrue($product->getTranslations()->equals($productForUpdate->getTranslations()));
        self::assertSame(2, $product->getVersion()->value(), "Version mustn't changed");
        self::assertFalse($product->getVersion()->equals($productForUpdate->getVersion()), "Version mustn't changed");
        self::assertFalse($product->getCreatedBy()->equals($productForUpdate->getCreatedBy()), "Created by mustn't changed");
        foreach ($productForUpdate->getPrices() as $expectedPrice) {
            $actualPrice = $product->getPrices()->getByCurrencyAndType(
                currency: $expectedPrice->getPrice()->getCurrency(),
                type: $expectedPrice->getType()->value()
            );
            self::assertNotNull($actualPrice);
            self::assertTrue($expectedPrice->getPrice()->equals($actualPrice->getPrice()));
            self::assertTrue($expectedPrice->getTax()->equals($actualPrice->getTax()));
            self::assertTrue($expectedPrice->getTaxIncluded()->equals($actualPrice->getTaxIncluded()));
            $this->assertVoEqualsOrNull($expectedPrice->getValidityPeriod(), $actualPrice->getValidityPeriod());
            if (
                $expectedPrice->getPrice()->getCurrency() === $existingPrice->getPrice()->getCurrency()
                && $expectedPrice->getType()->value() === $existingPrice->getType()->value()
            ) {
                self::assertTrue($productForUpdate->getUpdatedBy()->equals($actualPrice->getUpdatedBy()));
            } else {
                self::assertTrue($productForUpdate->getUpdatedBy()->equals($actualPrice->getCreatedBy()));
                self::assertNull($actualPrice->getUpdatedBy(), 'Updated by must be null for new price');
            }
        }
        self::assertTrue($product->getCategoryIds()->equals($productForUpdate->getCategoryIds()));
        self::assertTrue($product->getAttributeValues()->equals($productForUpdate->getAttributeValues()));
        self::assertCount(0, $product->getImages());
        self::assertTrue($product->getUpdatedBy()->equals($productForUpdate->getUpdatedBy()));
    }

    private function createFactory(): ProductApplicationFactory
    {
        return new ProductApplicationFactory(
            attributeReadRepository: $this->attributeReadRepository,
            providers: $this->container,
        );
    }
}
