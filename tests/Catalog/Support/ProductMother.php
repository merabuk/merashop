<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Factory\Contract\ProductFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id as CategoryId;
use App\Catalog\Domain\ValueObject\Product\AttributeValueCollection;
use App\Catalog\Domain\ValueObject\Product\CategoryIdCollection;
use App\Catalog\Domain\ValueObject\Product\Id as ProductId;
use App\Catalog\Domain\ValueObject\Product\ImageCollection;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Catalog\Domain\ValueObject\Product\Translations;
use App\Catalog\Domain\ValueObject\Product\Ulid;
use App\Catalog\Domain\ValueObject\Product\Version;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Faker\Factory;
use Faker\Generator;

final readonly class ProductMother
{
    public const string DEFAULT_ULID = '01KKPVEX17ZDADKJ360HN6GNKQ';
    public const string DEFAULT_ADMIN_ULID = '01KHVRCA679BJ6PBXX5N3G6RR5';

    public function __construct(
        private ProductFactoryInterface $productFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
        private Factory $fakerFactory,
    ) {
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     * @param ?ProductPrice[]                                           $prices
     * @param ?CategoryId[]                                             $categoryIds
     * @param ?ProductAttributeValue[]                                  $attributeValues
     */
    public static function createWithData(
        ?string $ulid = null,
        ?string $sku = null,
        ?StatusEnum $status = null,
        ?array $translations = null,
        ?int $version = null,
        ?string $createdByUlid = null,
        ?array $prices = null,
        ?string $updatedByUlid = null,
        ?array $categoryIds = null,
        ?array $attributeValues = null,
        ?array $images = null,
        ?int $id = null,
    ): Product {
        return new Product(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            sku: $sku ? Sku::fromString($sku) : Sku::fromString('TEST-SKU'),
            status: Status::fromEnum($status ?? StatusEnum::Active),
            translations: Translations::fromArray($translations ?: self::makeFakeTranslations()),
            version: $version ? Version::fromInt($version) : Version::initial(),
            createdBy: AdminUlid::fromString($createdByUlid ?? self::DEFAULT_ADMIN_ULID),
            prices: PriceCollection::fromArray($prices ?? self::makeFakePrices()),
            categoryIds: CategoryIdCollection::fromArray($categoryIds ?? []),
            attributeValues: AttributeValueCollection::fromArray($attributeValues ?? []),
            images: ImageCollection::fromArray($images ?? []),
            updatedBy: $updatedByUlid ? AdminUlid::fromString($updatedByUlid) : null,
            id: $id ? ProductId::fromInt($id) : null,
        );
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     * @param ?ProductPrice[]                                           $prices
     * @param ?CategoryId[]                                             $categoryIds
     * @param ?ProductAttributeValue[]                                  $attributeValues
     */
    public function create(
        ?string $ulid = null,
        ?string $sku = null,
        ?StatusEnum $status = null,
        ?array $translations = null,
        ?array $prices = null,
        ?string $createdByUlid = null,
        ?array $categoryIds = null,
        ?array $attributeValues = null,
    ): Product {
        return $this->productFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            sku: $sku ?? $this->faker->unique()->regexify('[A-Z]{3}-\d{2}-[A-Z]{3}-\d{2}'),
            status: $status ?? StatusEnum::Active,
            translations: $translations ?? $this->makeTranslations(),
            prices: $prices ?? self::makeFakePrices(),
            createdByUlid: $createdByUlid ?? $this->ulidGenerator->next(),
            categoryIds: $categoryIds ?? [],
            attributeValues: $attributeValues ?? [],
        );
    }

    /**
     * @return Product[]
     */
    public function createMany(int $count): array
    {
        $attributes = [];
        for ($i = 0; $i < $count; ++$i) {
            $attributes[] = $this->create();
        }

        return $attributes;
    }

    /**
     * @return array<string, array{name: string, description?: string}>
     */
    private function makeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $faker = $this->fakerFactory->create($locale->value);

            $translations[$locale->value] = self::makeTranslationItem(
                name: $faker->word(),
                description: $faker->sentence(),
            );
        }

        return $translations;
    }

    private static function makeFakeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale->value] = self::makeTranslationItem(
                name: $locale->value.' name',
                description: $locale->value.' description',
            );
        }

        return $translations;
    }

    private static function makeTranslationItem(
        string $name,
        string $description,
    ): array {
        return [
            'name' => $name,
            'description' => $description,
        ];
    }

    /**
     * @return ProductPrice[]
     */
    private static function makeFakePrices(): array
    {
        $currencies = CurrencyEnum::cases();
        $productPriceTypes = [TypeEnum::Regular, TypeEnum::Cost];

        $prices = [];
        foreach ($currencies as $currency) {
            foreach ($productPriceTypes as $type) {
                $prices[] = ProductPriceMother::createWithData(currency: $currency, type: $type);
            }
        }

        return $prices;
    }
}
