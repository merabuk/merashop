<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
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
        $prices = self::getValidPrices();
        $createdBy = AdminUlid::fromString(ProductMother::DEFAULT_ADMIN_ULID);
        $categoryIds = self::getValidCategoryIds();
        $attributeValues = self::getValidAttributeValues();

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
            $translation = $product->getTranslations()->get($locale);
            self::assertNotNull($translation);
            self::assertSame($translation->name, $translation->name);
            self::assertSame($translation->description, $translation->description);
        }
        self::assertCount(count($prices), $product->getPrices());
        foreach ($prices as $price) {
            $actualPrice = array_find(
                array: $product->getPrices(),
                callback: fn (ProductPrice $p) => $p->getType()->equals($price->getType())
                    && $p->getPrice()->equals($price->getPrice())
            );
            self::assertNotNull($actualPrice);
            self::assertNull($actualPrice->getId());
            self::assertTrue($price->getTax()->equals($actualPrice->getTax()));
            self::assertTrue($price->getTaxIncluded()->equals($actualPrice->getTaxIncluded()));
            $this->assertVoEqualsOrNull($price->getValidFrom(), $actualPrice->getValidFrom());
            $this->assertVoEqualsOrNull($price->getValidTo(), $actualPrice->getValidTo());
        }
        self::assertSame(1, $product->getVersion()->value());
        self::assertTrue($product->getCreatedBy()->equals($createdBy));
        self::assertNull($product->getUpdatedBy());
        self::assertCount(count($categoryIds), $product->getCategoryIds());
        $actualCategoryIds = array_map(fn (CategoryId $id) => $id->value(), $product->getCategoryIds());
        $actualCategoryIdsCheck = array_combine($actualCategoryIds, $actualCategoryIds);
        foreach ($categoryIds as $categoryId) {
            self::assertSame($categoryId->value(), $actualCategoryIdsCheck[$categoryId->value()]);
        }
        self::assertCount(count($attributeValues), $product->getAttributeValues());
        foreach ($attributeValues as $attributeValue) {
            $actualAttributeValue = array_find(
                array: $attributeValues,
                callback: fn (ProductAttributeValue $pav) => $pav->getAttributeId()->equals($attributeValue->getAttributeId())
            );
            self::assertNotNull($actualAttributeValue);
            self::assertTrue($attributeValue->getValue()->equals($actualAttributeValue->getValue()));
        }
        self::assertEmpty($product->getImages());
    }

    /**
     * @return array<string, array{name: string, description?: string}>
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
