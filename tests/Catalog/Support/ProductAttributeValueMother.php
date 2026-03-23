<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Factory\Contract\ProductAttributeValueFactoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Id as ProductAttributeId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\AttributeValueInterface;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\ProductAttributeValueNormalizer;
use App\Shared\Domain\Enum\LocaleEnum;
use Faker\Factory;
use Faker\Generator;
use RuntimeException;

final readonly class ProductAttributeValueMother
{
    public function __construct(
        private ProductAttributeValueFactoryInterface $productAttributeValueFactory,
        private Generator $faker,
        private Factory $fakerFactory,
        private ProductAttributeValueNormalizer $normalizer,
    ) {
    }

    public static function createWithData(
        int $attributeId,
        AttributeTypeEnum $attributeType,
        ?int $optionId = null,
        mixed $value = null,
        ?int $id = null,
    ): ProductAttributeValue {
        return new ProductAttributeValue(
            attributeId: AttributeId::fromInt($attributeId),
            attributeOptionId: $optionId ? AttributeOptionId::fromInt($optionId) : null,
            value: self::getFakeAttributeValue($attributeType, $value),
            id: $id ? ProductAttributeId::fromInt($id) : null
        );
    }

    public function create(
        int $attributeId,
        AttributeTypeEnum $attributeType,
        ?int $optionId = null,
        mixed $value = null,
    ): ProductAttributeValue {
        $attributeValue = $this->getAttributeValue($attributeType, $value);

        return $this->productAttributeValueFactory->createForTest(
            attributeId: $attributeId,
            attributeOptionId: $optionId,
            value: $attributeValue,
        );
    }

    private function getAttributeValue(
        AttributeTypeEnum $attributeType,
        mixed $value,
    ): ?AttributeValueInterface {
        $arrayValue = match ($attributeType) {
            AttributeTypeEnum::String,
            AttributeTypeEnum::Text => is_null($value) ? $this->makeTranslations() : (array) $value,
            AttributeTypeEnum::Integer => ['value' => $value ?? $this->faker->numberBetween(1, 1000)],
            AttributeTypeEnum::Boolean => ['value' => $value ?? $this->faker->boolean()],
            AttributeTypeEnum::Select,
            AttributeTypeEnum::MultiSelect => [],
            AttributeTypeEnum::Color => ['value' => $value ?? $this->faker->hexColor()],
            AttributeTypeEnum::Date => ['value' => $value ?? $this->faker->date()],
            AttributeTypeEnum::Url => ['value' => $value ?? $this->faker->url()],
            AttributeTypeEnum::Dimension => is_null($value) ? [
                'magnitude' => $this->faker->randomFloat(2, 1, 1000),
                'unit' => $this->faker->randomElement(['cm', 'm', 'in', 'ft']),
            ] : (array) $value,
            default => throw new RuntimeException(sprintf('Unsupported attribute type: %s', $attributeType->value)),
        };

        return $this->normalizer->denormalize($attributeType, $arrayValue);
    }

    private static function getFakeAttributeValue(
        AttributeTypeEnum $attributeType,
        mixed $value,
    ): ?AttributeValueInterface {
        $arrayValue = match ($attributeType) {
            AttributeTypeEnum::String,
            AttributeTypeEnum::Text => is_null($value) ? self::makeFakeTranslations() : (array) $value,
            AttributeTypeEnum::Integer => ['value' => $value ?? 42],
            AttributeTypeEnum::Boolean => ['value' => $value ?? true],
            AttributeTypeEnum::Select,
            AttributeTypeEnum::MultiSelect => [],
            AttributeTypeEnum::Color => ['value' => $value ?? '#ffffff'],
            AttributeTypeEnum::Date => ['value' => $value ?? '2023-01-01'],
            AttributeTypeEnum::Url => ['value' => $value ?? 'https://example.com'],
            AttributeTypeEnum::Dimension => is_null($value) ? [
                'magnitude' => 1234.5,
                'unit' => 'cm',
            ] : (array) $value,
            default => throw new RuntimeException(sprintf('Unsupported attribute type: %s', $attributeType->value)),
        };

        return new ProductAttributeValueNormalizer()->denormalize($attributeType, $arrayValue);
    }

    private function makeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $faker = $this->fakerFactory->create($locale->value);
            $translations[$locale->value] = $faker->text();
        }

        return $translations;
    }

    private static function makeFakeTranslations(): array
    {
        $locales = LocaleEnum::cases();
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale->value] = $locale->value.' translation value';
        }

        return $translations;
    }
}
