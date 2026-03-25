<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\CategoryIdCollection;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use App\Tests\Catalog\Support\ProductImageMother;
use App\Tests\Catalog\Support\ProductMother;
use App\Tests\Catalog\Support\ProductPriceMother;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    use ValueObjectAssertionTrait;

    public function testItCreatesProduct(): void
    {
        $ulid = Ulid::fromString(ProductMother::DEFAULT_ULID);
        $sku = Sku::fromString('TEST-SKU');
        $status = Status::active();
        $translations = Translations::fromArray(self::getValidTranslations());
        $prices = PriceCollection::fromArray(self::getValidPrices());
        $createdBy = AdminUlid::fromString(ProductMother::DEFAULT_ADMIN_ULID);
        $categoryIds = CategoryIdCollection::fromArray(self::getValidCategoryIds());
        $attributeValues = AttributeValueCollection::fromArray(self::getValidAttributeValues());

        $product = Product::create(
            ulid: $ulid,
            sku: $sku,
            status: $status,
            translations: $translations,
            prices: $prices,
            createdBy: $createdBy,
            categoryIds: $categoryIds,
            attributeValues: $attributeValues,
        );

        self::assertNull($product->getId());
        self::assertTrue($product->getUlid()->equals($ulid));
        self::assertTrue($product->getStatus()->equals($status));
        self::assertCount($translations->count(), $product->getTranslations());
        foreach ($translations as $locale => $translation) {
            $actualTranslation = $product->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->name, $actualTranslation->name);
            self::assertSame($translation->description, $actualTranslation->description);
        }
        self::assertCount($prices->count(), $product->getPrices());
        foreach ($prices as $price) {
            $actualPrice = array_find(
                array: $product->getPrices()->all(),
                callback: fn (ProductPrice $p) => $p->getType()->equals($price->getType())
                    && $p->getPrice()->equals($price->getPrice())
            );
            self::assertNotNull($actualPrice);
            self::assertNull($actualPrice->getId());
            self::assertTrue($price->getTax()->equals($actualPrice->getTax()));
            self::assertTrue($price->getTaxIncluded()->equals($actualPrice->getTaxIncluded()));
            $this->assertVoEqualsOrNull($price->getValidityPeriod(), $actualPrice->getValidityPeriod());
        }
        self::assertSame(1, $product->getVersion()->value());
        self::assertTrue($product->getCreatedBy()->equals($createdBy));
        self::assertNull($product->getUpdatedBy());
        self::assertCount($categoryIds->count(), $product->getCategoryIds());
        $actualCategoryIds = array_map(fn (CategoryId $id) => $id->value(), $product->getCategoryIds()->all());
        $actualCategoryIdsCheck = array_combine($actualCategoryIds, $actualCategoryIds);
        foreach ($categoryIds as $categoryId) {
            self::assertSame($categoryId->value(), $actualCategoryIdsCheck[$categoryId->value()]);
        }
        self::assertCount($attributeValues->count(), $product->getAttributeValues());
        foreach ($attributeValues as $attributeValue) {
            $actualAttributeValue = array_find(
                array: $product->getAttributeValues()->all(),
                callback: fn (ProductAttributeValue $pav) => $pav->getAttributeId()->equals($attributeValue->getAttributeId())
            );
            self::assertNotNull($actualAttributeValue);
            self::assertTrue($attributeValue->getValue()->equals($actualAttributeValue->getValue()));
        }
        self::assertEmpty($product->getImages());
    }

    public function testItUpdateChangesState(): void
    {
        $product = ProductMother::createWithData(
            sku: 'OLD-TEST-SKU',
            status: StatusEnum::Draft,
            translations: self::getValidTranslations(),
            prices: self::getValidPrices(),
            categoryIds: [],
            attributeValues: [],
        );

        $newSku = Sku::fromString('NEW-TEST-SKU');
        $newStatus = Status::fromEnum(StatusEnum::Active);
        $newTranslations = Translations::fromArray([
            'en' => ['name' => 'New test product', 'description' => 'New test product description'],
            'uk' => ['name' => 'Новий тестовий продукт', 'description' => 'Новий тестовий опис продукту'],
        ]);
        $newPrices = PriceCollection::fromArray([
            ProductPriceMother::createWithData(
                currency: CurrencyEnum::UAH,
                type: TypeEnum::Regular,
            ),
            ProductPriceMother::createWithData(
                currency: CurrencyEnum::USD,
                type: TypeEnum::Regular,
            ),
        ]);
        $updatedBy = AdminUlid::fromString(ProductMother::DEFAULT_ADMIN_ULID);
        $newCategoryIds = CategoryIdCollection::fromArray(self::getValidCategoryIds());
        $newAttributeValues = AttributeValueCollection::fromArray(self::getValidAttributeValues());

        $product->update(
            sku: $newSku,
            status: $newStatus,
            translations: $newTranslations,
            updatedBy: $updatedBy,
            prices: $newPrices,
            categoryIds: $newCategoryIds,
            attributeValues: $newAttributeValues,
        );

        self::assertTrue($product->getSku()->equals($newSku));
        self::assertTrue($product->getStatus()->equals($newStatus));
        self::assertCount($newTranslations->count(), $product->getTranslations());
        foreach ($newTranslations as $locale => $translation) {
            $actualTranslation = $product->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->name, $actualTranslation->name);
            self::assertSame($translation->description, $actualTranslation->description);
        }
        self::assertCount($newPrices->count(), $product->getPrices());
        foreach ($newPrices as $price) {
            $actualPrice = array_find(
                array: $product->getPrices()->all(),
                callback: fn (ProductPrice $p) => $p->getType()->equals($price->getType())
                    && $p->getPrice()->equals($price->getPrice())
            );
            self::assertNotNull($actualPrice);
            self::assertTrue($price->getTax()->equals($actualPrice->getTax()));
            self::assertTrue($price->getTaxIncluded()->equals($actualPrice->getTaxIncluded()));
            $this->assertVoEqualsOrNull($price->getValidityPeriod(), $actualPrice->getValidityPeriod());
        }
        self::assertTrue($product->getUpdatedBy()->equals($updatedBy));
        self::assertCount($newCategoryIds->count(), $product->getCategoryIds());
        $actualCategoryIds = array_map(fn (CategoryId $id) => $id->value(), $product->getCategoryIds()->all());
        $actualCategoryIdsCheck = array_combine($actualCategoryIds, $actualCategoryIds);
        foreach ($newCategoryIds as $categoryId) {
            self::assertSame($categoryId->value(), $actualCategoryIdsCheck[$categoryId->value()]);
        }
        self::assertCount($newAttributeValues->count(), $product->getAttributeValues());
        foreach ($newAttributeValues as $attributeValue) {
            $actualAttributeValue = array_find(
                array: $product->getAttributeValues()->all(),
                callback: fn (ProductAttributeValue $pav) => $pav->getAttributeId()->equals($attributeValue->getAttributeId())
            );
            self::assertNotNull($actualAttributeValue);
            self::assertTrue($attributeValue->getValue()->equals($actualAttributeValue->getValue()));
        }
    }

    public function testAddImage(): void
    {
        $product = ProductMother::createWithData();

        self::assertCount(0, $product->getImages());

        $image1 = ProductImageMother::createWithData(
            isMain: false
        );

        $product->addImage($image1);

        self::assertCount(1, $product->getImages());
        self::assertSame($image1, $product->getImages()->all()[0]);
        self::assertTrue($image1->isMain()->isTrue());

        $product->addImage($image1);

        self::assertCount(1, $product->getImages());

        $image2 = ProductImageMother::createWithData(
            ulid: '01KKTVY7D6D7S1BCSBB3GQA8B3',
            isMain: true
        );

        $product->addImage($image2);

        self::assertCount(2, $product->getImages());
        self::assertSame($image2, $product->getImages()->all()[1]);
        self::assertTrue($image1->isMain()->isFalse());
        self::assertTrue($image2->isMain()->isTrue());
    }

    public function testItSetsImages(): void
    {
        $product = ProductMother::createWithData();

        self::assertCount(0, $product->getImages());

        $images = ImageCollection::fromArray([
            ProductImageMother::createWithData(
                isMain: true
            ),
        ]);

        $product->setImages($images);

        self::assertCount(1, $product->getImages());
        self::assertTrue($product->getImages()->equals($images));
    }

    /**
     * @return array<string, array{name: string, description: string}>
     */
    private static function getValidTranslations(): array
    {
        return [
            'en' => ['name' => 'Test product', 'description' => 'Test product description'],
            'uk' => ['name' => 'Тестовий продукт', 'description' => 'Тестовий опис продукту'],
        ];
    }

    /**
     * @return ProductPrice[]
     */
    private static function getValidPrices(): array
    {
        $currencies = CurrencyEnum::cases();
        $productPriceTypes = TypeEnum::cases();

        $prices = [];
        foreach ($currencies as $currency) {
            foreach ($productPriceTypes as $type) {
                $prices[] = ProductPriceMother::createWithData(currency: $currency, type: $type);
            }
        }

        return $prices;
    }

    /**
     * @return CategoryId[]
     */
    private static function getValidCategoryIds(?array $categoryIds = null): array
    {
        $categoryIds ??= [123, 456, 789];

        return array_map(fn (int $id) => CategoryId::fromInt($id), $categoryIds);
    }

    /**
     * @return ProductAttributeValue[]
     */
    private static function getValidAttributeValues(?array $attributeIds = null): array
    {
        $attributeIds ??= [321, 654, 987];

        return array_map(fn (int $id) => ProductAttributeValueMother::createWithData(attributeId: $id), $attributeIds);
    }
}
