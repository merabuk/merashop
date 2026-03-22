<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Exception\ProductAttributeValue\UnsupportedAttributeTypeException;
use App\Catalog\Domain\ValueObject\Attribute\Id as AttributeId;
use App\Catalog\Domain\ValueObject\ProductAttribute\ArrayValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\BooleanValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\IntegerValue;
use App\Catalog\Domain\ValueObject\ProductAttribute\StringValue;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ProductAttributeValueTest extends TestCase
{
    #[DataProvider('productAttributeValueProvider')]
    public function testItResolvesProductAttributeValue(mixed $rawValue, string $expectedVoClass): void
    {
        $attributeValue = ProductAttributeValue::resolveValue($rawValue);

        self::assertInstanceOf($expectedVoClass, $attributeValue);
        self::assertSame($rawValue, $attributeValue->value());
    }

    #[DataProvider('productAttributeValueProvider')]
    public function testItCreatesProductAttributeValue(mixed $rawValue, string $expectedVoClass): void
    {
        $attributeId = AttributeId::fromInt(123);
        $attributeValue = ProductAttributeValue::resolveValue($rawValue);

        $productAttributeValue = ProductAttributeValue::create(
            attributeId: $attributeId,
            value: $attributeValue
        );

        self::assertNull($productAttributeValue->getId());
        self::assertTrue($productAttributeValue->getAttributeId()->equals($attributeId));
        self::assertInstanceOf($expectedVoClass, $productAttributeValue->getValue());
        self::assertTrue($productAttributeValue->getValue()->equals($attributeValue));
    }

    #[DataProvider('productAttributeValueProvider')]
    public function testItCreatesWithRawValue(mixed $rawValue, string $expectedVoClass): void
    {
        $attributeId = AttributeId::fromInt(1);

        $productAttributeValue = ProductAttributeValue::createWithRawValue(
            attributeId: $attributeId,
            value: $rawValue
        );

        self::assertTrue($productAttributeValue->getAttributeId()->equals($attributeId));
        self::assertInstanceOf($expectedVoClass, $productAttributeValue->getValue());
        self::assertSame($rawValue, $productAttributeValue->getValue()->value());
    }

    public static function productAttributeValueProvider(): iterable
    {
        yield 'string' => [
            'rawValue' => 'Some text',
            'expectedVoClass' => StringValue::class,
        ];
        yield 'int' => [
            'rawValue' => 42,
            'expectedVoClass' => IntegerValue::class,
        ];
        yield 'boolean' => [
            'rawValue' => true,
            'expectedVoClass' => BooleanValue::class,
        ];
        yield 'array' => [
            'rawValue' => ['a', 'b'],
            'expectedVoClass' => ArrayValue::class,
        ];
    }

    #[DataProvider('notSupportedValueTypeProvider')]
    public function testThrowsExceptionWhenNotSupportedValueType(mixed $value): void
    {
        $this->expectException(UnsupportedAttributeTypeException::class);

        ProductAttributeValue::resolveValue($value);
    }

    public static function notSupportedValueTypeProvider(): iterable
    {
        yield 'float value' => [1.23];
        yield 'object value' => [new stdClass()];
        yield 'null value' => [null];
    }

    public function testItUpdatesAttributeValue(): void
    {
        $productAttributeValue = ProductAttributeValueMother::createWithData(
            attributeId: 123,
            value: 'old value'
        );

        $newAttributeValue = ProductAttributeValue::resolveValue('new value');

        $productAttributeValue->updateValue($newAttributeValue);

        self::assertTrue($productAttributeValue->getValue()->equals($newAttributeValue));
    }
}
