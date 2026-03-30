<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum as ProductPriceTypeEnum;
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
use DateTimeImmutable;
use Faker\Factory;
use Faker\Generator;
use RuntimeException;

final readonly class ProductMother
{
    public const string DEFAULT_ULID = '01KKPVEX17ZDADKJ360HN6GNKQ';
    public const string DEFAULT_ADMIN_ULID = '01KHVRCA679BJ6PBXX5N3G6RR5';

    public function __construct(
        private ProductFactoryInterface $productFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
        private Factory $fakerFactory,
        private CategoryFixture $categoryFixture,
        private AttributeOptionMother $attributeOptionMother,
        private AttributeFixture $attributeFixture,
        private ProductAttributeValueMother $productAttributeValueMother,
    ) {
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     * @param ?ProductPrice[]                                           $prices
     * @param ?CategoryId[]                                             $categoryIds
     * @param ?ProductAttributeValue[]                                  $attributeValues
     * @param ?ProductImage[]                                           $images
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
        bool $withFakeIds = false,
    ): Product {
        $id ??= $withFakeIds ? 123 : null;

        return new Product(
            ulid: Ulid::fromString($ulid ?? self::DEFAULT_ULID),
            sku: $sku ? Sku::fromString($sku) : Sku::fromString('TEST-SKU'),
            status: Status::fromEnum($status ?? StatusEnum::Active),
            translations: Translations::fromArray($translations ?: self::makeFakeTranslations()),
            version: $version ? Version::fromInt($version) : Version::initial(),
            createdBy: AdminUlid::fromString($createdByUlid ?? self::DEFAULT_ADMIN_ULID),
            prices: PriceCollection::fromArray($prices ?? self::makeFakePrices($withFakeIds)),
            categoryIds: CategoryIdCollection::fromArray($categoryIds ?? self::makeFakeCategoryIds()),
            attributeValues: AttributeValueCollection::fromArray($attributeValues ?? self::makeFakeAttributeValues()),
            images: ImageCollection::fromArray($images ?? self::makeFakeImages($withFakeIds)),
            updatedBy: $updatedByUlid ? AdminUlid::fromString($updatedByUlid) : null,
            id: $id ? ProductId::fromInt($id) : null,
        );
    }

    /**
     * @param ?array<string, array{name: string, description?: string}> $translations
     * @param ?ProductPrice[]                                           $prices
     * @param ?CategoryId[]                                             $categoryIds
     * @param ?ProductAttributeValue[]                                  $attributeValues
     * @param ?ProductImage[]                                           $images
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
        ?array $images = null,
    ): Product {
        $product = $this->productFactory->createForTest(
            ulid: $ulid ?? $this->ulidGenerator->next(),
            sku: $sku ?? $this->faker->unique()->regexify('[A-Z]{3}-\d{2}-[A-Z]{3}-\d{2}'),
            status: $status ?? StatusEnum::Active,
            translations: $translations ?? $this->makeTranslations(),
            prices: $prices ?? $this->makePrices(),
            createdByUlid: $createdByUlid ?? $this->ulidGenerator->next(),
            categoryIds: $categoryIds ?? [],
            attributeValues: $attributeValues ?? [],
        );

        if ($images) {
            $product->setImages(ImageCollection::fromArray($images));
        }

        return $product;
    }

    public function createFullFeatured(): Product
    {
        $product = $this->productFactory->createForTest(
            ulid: $this->ulidGenerator->next(),
            sku: $this->faker->unique()->regexify('[A-Z]{3}-\d{2}-[A-Z]{3}-\d{2}'),
            status: StatusEnum::Active,
            translations: $this->makeTranslations(),
            prices: $this->makePrices(),
            createdByUlid: $this->ulidGenerator->next(),
            categoryIds: $this->makeCategoryIds(),
            attributeValues: $this->makeAttributeValues(),
        );

        $product->setImages(ImageCollection::fromArray($this->makeImages()));

        return $product;
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
    private function makePrices(): array
    {
        $currencies = CurrencyEnum::cases();
        $productPriceTypes = ProductPriceTypeEnum::cases();

        $prices = [];
        foreach ($currencies as $currency) {
            foreach ($productPriceTypes as $type) {
                $isTimeLimited = ProductPriceTypeEnum::Sale === $type;

                if ($isTimeLimited) {
                    $format = DateTimeImmutable::ATOM;
                    $validFrom = new DateTimeImmutable(
                        $this->faker->dateTimeBetween('now', '+1 month')->format($format)
                    );
                    $validTo = new DateTimeImmutable(
                        $this->faker->dateTimeBetween($validFrom->format($format), '+2 month')->format($format)
                    );
                }

                $prices[] = ProductPriceMother::createWithData(
                    currency: $currency,
                    type: $type,
                    validFrom: $validFrom ?? null,
                    validTo: $validTo ?? null,
                );

                unset($validFrom, $validTo);
            }
        }

        return $prices;
    }

    /**
     * @return ProductPrice[]
     */
    private static function makeFakePrices(bool $withFakeIds): array
    {
        $currencies = CurrencyEnum::cases();
        $productPriceTypes = [ProductPriceTypeEnum::Regular, ProductPriceTypeEnum::Cost];

        $prices = [];
        $i = 0;
        foreach ($currencies as $currency) {
            foreach ($productPriceTypes as $type) {
                $isTimeLimited = ProductPriceTypeEnum::Sale === $type;

                if ($isTimeLimited) {
                    $validFrom = new DateTimeImmutable('2024-01-01 00:00:00');
                    $validTo = new DateTimeImmutable('2024-01-31 23:59:59');
                }

                $prices[] = ProductPriceMother::createWithData(
                    currency: $currency,
                    type: $type,
                    validFrom: $validFrom ?? null,
                    validTo: $validTo ?? null,
                    id: $withFakeIds ? 3330 + $i : null
                );
                ++$i;
            }
        }

        return $prices;
    }

    /**
     * @return CategoryId[]
     */
    private function makeCategoryIds(): array
    {
        $ids = [];
        for ($i = 0; $i < 3; ++$i) {
            $category = $this->categoryFixture->create();
            $ids[] = $category->getId() ?? throw new RuntimeException('Category ID is null');
        }

        return $ids;
    }

    private static function makeFakeCategoryIds(): array
    {
        $ids = [];
        for ($i = 0; $i < 3; ++$i) {
            $ids[] = CategoryId::fromInt(4440 + $i);
        }

        return $ids;
    }

    /**
     * @return ProductAttributeValue[]
     */
    private function makeAttributeValues(): array
    {
        $attributeTypes = AttributeTypeEnum::cases();

        $values = [];
        foreach ($attributeTypes as $type) {
            if (TypeEnum::Image === $type) {
                continue;
            }

            $options = null;
            if ($type->hasOptions()) {
                $options = [$this->attributeOptionMother->create()];
            }

            $attribute = $this->attributeFixture->create(type: $type, options: $options);
            $values[] = $this->productAttributeValueMother->create(
                attributeId: $attribute->getId()?->value() ?? throw new RuntimeException('Attribute ID is null'),
                attributeType: $attribute->getType()->value(),
                optionId: $attribute->getOptions()->all()[0]?->getId()->value() ?? null,
            );
        }

        return $values;
    }

    private static function makeFakeAttributeValues(): array
    {
        $attributeTypes = AttributeTypeEnum::cases();

        $attributeValues = [];
        foreach ($attributeTypes as $i => $type) {
            if (TypeEnum::Image === $type) {
                // not supported yet
                continue;
            }

            $attributeValues[] = ProductAttributeValueMother::createWithData(
                attributeId: 5550 + $i,
                attributeType: $type,
                optionId: $type->hasOptions() ? 6660 + $i : null
            );
        }

        return $attributeValues;
    }

    /**
     * @return ProductImage[]
     */
    private function makeImages(): array
    {
        $images = [];
        for ($i = 0; $i < 3; ++$i) {
            $image = ProductImageMother::createWithData(
                ulid: $this->ulidGenerator->next(),
                sortOrder: $i + 1,
                isMain: 0 === $i,
            );
            $images[] = $image;
        }

        return $images;
    }

    private static function makeFakeImages(bool $withFakeIds): array
    {
        $ulids = [
            '01KKTVY7D6D7S1BCSBB3GQA8B3',
            '01KKTVY7D6D7S1BCSBB3GQA8B4',
            '01KKTVY7D6D7S1BCSBB3GQA8B5',
        ];

        $images = [];
        foreach ($ulids as $i => $ulid) {
            $images[] = ProductImageMother::createWithData(
                ulid: $ulid,
                path: 'products/2024/03/19/img_'.($i + 1).'.jpg',
                sortOrder: $i + 1,
                isMain: 0 === $i,
                id: $withFakeIds ? 2220 + $i : null
            );
        }

        return $images;
    }
}
