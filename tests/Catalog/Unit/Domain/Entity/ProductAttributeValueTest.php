<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\AttributeOption\Id as AttributeOptionId;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\ColorValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DateValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DimensionValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\FloatValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedStringValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedTextValue;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\UrlValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\ProductAttributeValueNormalizer;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductAttributeValueTest extends TestCase
{
    use ValueObjectAssertionTrait;

    private ProductAttributeValueNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ProductAttributeValueNormalizer();
    }

    #[DataProvider('productAttributeValueProvider')]
    public function testItCreatesProductAttributeValue(
        TypeEnum $type,
        ?int $optionId,
        ?array $rawValue,
        ?string $expectedVoClass,
    ): void {
        $attributeId = AttributeId::fromInt(123);
        $attributeOptionId = $optionId ? AttributeOptionId::fromInt($optionId) : null;
        $attributeValue = $this->normalizer->denormalize($type, $rawValue, $attributeOptionId);

        $productAttributeValue = ProductAttributeValue::create(
            attributeId: $attributeId,
            attributeOptionId: $attributeOptionId,
            value: $attributeValue
        );

        self::assertNull($productAttributeValue->getId());
        self::assertTrue($productAttributeValue->getAttributeId()->equals($attributeId));
        $this->assertVoEqualsOrNull($attributeOptionId, $productAttributeValue->getAttributeOptionId());
        if (null !== $expectedVoClass) {
            self::assertInstanceOf($expectedVoClass, $productAttributeValue->getValue());
        }
        $this->assertVoEqualsOrNull($attributeValue, $productAttributeValue->getValue());
    }

    public static function productAttributeValueProvider(): iterable
    {
        yield 'localized string' => [
            'type' => TypeEnum::String,
            'optionId' => null,
            'rawValue' => [
                'en' => 'string value',
                'uk' => 'строкове значення',
            ],
            'expectedVoClass' => LocalizedStringValue::class,
        ];
        yield 'localized text' => [
            'type' => TypeEnum::Text,
            'optionId' => null,
            'rawValue' => [
                'en' => 'large text value',
                'uk' => 'велике текстове значення',
            ],
            'expectedVoClass' => LocalizedTextValue::class,
        ];
        yield 'int' => [
            'type' => TypeEnum::Integer,
            'optionId' => null,
            'rawValue' => ['value' => 42],
            'expectedVoClass' => IntegerValue::class,
        ];
        yield 'float' => [
            'type' => TypeEnum::Float,
            'optionId' => null,
            'rawValue' => ['value' => 1.23],
            'expectedVoClass' => FloatValue::class,
        ];
        yield 'boolean' => [
            'type' => TypeEnum::Boolean,
            'optionId' => null,
            'rawValue' => ['value' => true],
            'expectedVoClass' => BooleanValue::class,
        ];
        yield 'select' => [
            'type' => TypeEnum::Select,
            'optionId' => 321,
            'rawValue' => [],
            'expectedVoClass' => null,
        ];
        yield 'multiselect' => [
            'type' => TypeEnum::MultiSelect,
            'optionId' => 456,
            'rawValue' => [],
            'expectedVoClass' => null,
        ];
        yield 'color' => [
            'type' => TypeEnum::Color,
            'optionId' => null,
            'rawValue' => ['value' => '#ffffff'],
            'expectedVoClass' => ColorValue::class,
        ];
        yield 'date' => [
            'type' => TypeEnum::Date,
            'optionId' => null,
            'rawValue' => ['value' => '2023-01-01'],
            'expectedVoClass' => DateValue::class,
        ];
        yield 'url' => [
            'type' => TypeEnum::Url,
            'optionId' => null,
            'rawValue' => ['value' => 'https://example.com'],
            'expectedVoClass' => UrlValue::class,
        ];
        yield 'dimension' => [
            'type' => TypeEnum::Dimension,
            'optionId' => 789,
            'rawValue' => ['magnitude' => 1234.5],
            'expectedVoClass' => DimensionValue::class,
        ];
    }

    public function testItCreatesWithOption(): void
    {
        $attributeId = AttributeId::fromInt(1);
        $attributeOptionId = AttributeOptionId::fromInt(2);

        $productAttributeValue = ProductAttributeValue::createWithOption(
            attributeId: $attributeId,
            attributeOptionId: $attributeOptionId
        );

        self::assertTrue($productAttributeValue->getAttributeId()->equals($attributeId));
        self::assertTrue($productAttributeValue->getAttributeOptionId()->equals($attributeOptionId));
    }

    public function testItCreatesWithValue(): void
    {
        $attributeId = AttributeId::fromInt(1);
        $value = IntegerValue::fromInt(42);

        $productAttributeValue = ProductAttributeValue::createWithValue(
            attributeId: $attributeId,
            value: $value
        );

        self::assertTrue($productAttributeValue->getAttributeId()->equals($attributeId));
        self::assertTrue($productAttributeValue->getValue()->equals($value));
    }

    public function testItUpdatesAttributeValue(): void
    {
        $type = TypeEnum::Integer;
        $productAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: 123,
            attributeType: $type,
            value: 456
        );

        $newAttributeValue = $this->normalizer->denormalize($type, ['value' => 789]);

        $productAttributeValue->updateValue($newAttributeValue);

        self::assertTrue($productAttributeValue->getValue()->equals($newAttributeValue));
    }
}
