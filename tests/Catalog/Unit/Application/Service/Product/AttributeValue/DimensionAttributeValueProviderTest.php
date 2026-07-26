<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\DimensionAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Application\Service\Product\AttributeValue\DimensionAttributeValueProvider;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\DimensionValue;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class DimensionAttributeValueProviderTest extends BaseUnitTest
{
    private const TypeEnum TYPE = TypeEnum::Dimension;

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(self::TYPE->value, $this->createProvider()::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $option = AttributeOptionMother::createWithData(id: 456);
        $attribute = AttributeMother::createWithData(type: self::TYPE, options: [$option], id: 123);
        $data = self::getData(unitOptionId: $option->getId()->value());

        $results = $this->createProvider()->handle($attribute, $data, $attribute->getCreatedBy());

        self::assertCount(1, $results);
        $result = $results[0];
        self::assertInstanceOf(ProductAttributeValue::class, $result);
        $value = $result->getValue();
        self::assertInstanceOf(DimensionValue::class, $value);
        self::assertSame($data->magnitude, $value->magnitude());
        self::assertSame($data->unitOptionId, $value->getUnitOptionId()->value());
        self::assertTrue($option->getId()->equals($result->getAttributeOptionId()));
        self::assertTrue($attribute->getCreatedBy()->equals($result->getCreatedBy()));
    }

    #[DataProvider('invalidDataProvider')]
    public function testThrowExceptionsWhenHasInvalidData(
        Attribute $attribute,
        AttributeValueDataInterface $data,
        string $expectedException,
    ): void {
        $this->expectException($expectedException);

        $this->createProvider()->handle($attribute, $data, $attribute->getCreatedBy());
    }

    public static function invalidDataProvider(): iterable
    {
        $unitOptionId = 456;

        yield 'attribute type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Integer, id: 123),
            'data' => self::getData(),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'data type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE, options: [
                AttributeOptionMother::createWithData(id: $unitOptionId),
            ], id: 123),
            'data' => new IntegerAttributeValueData(value: 123),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'unit option not found' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE, options: [
                AttributeOptionMother::createWithData(id: $unitOptionId),
            ], id: 123),
            'data' => self::getData(unitOptionId: 789),
            'expectedException' => AttributeOptionNotFoundException::class,
        ];
    }

    private static function getData(float $value = 1234.5, int $unitOptionId = 456): DimensionAttributeValueData
    {
        return new DimensionAttributeValueData(magnitude: $value, unitOptionId: $unitOptionId);
    }

    private function createProvider(): DimensionAttributeValueProvider
    {
        return new DimensionAttributeValueProvider();
    }
}
