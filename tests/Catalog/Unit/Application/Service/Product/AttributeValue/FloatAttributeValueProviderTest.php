<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\FloatAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Application\Service\Product\AttributeValue\FloatAttributeValueProvider;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\FloatValue;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class FloatAttributeValueProviderTest extends BaseUnitTest
{
    private const TypeEnum TYPE = TypeEnum::Float;

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(self::TYPE->value, $this->createProvider()::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $attribute = AttributeMother::createWithData(type: self::TYPE, id: 123);
        $data = self::getData();

        $results = $this->createProvider()->handle($attribute, $data, $attribute->getCreatedBy());

        self::assertCount(1, $results);
        $result = $results[0];
        self::assertInstanceOf(ProductAttributeValue::class, $result);
        $value = $result->getValue();
        self::assertInstanceOf(FloatValue::class, $value);
        self::assertSame($data->value, $value->value());
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
        yield 'attribute type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Integer),
            'data' => self::getData(),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'data type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE),
            'data' => new IntegerAttributeValueData(value: 123),
            'expectedException' => InvalidArgumentException::class,
        ];
    }

    private static function getData(float $value = 123.45): FloatAttributeValueData
    {
        return new FloatAttributeValueData(value: $value);
    }

    private function createProvider(): FloatAttributeValueProvider
    {
        return new FloatAttributeValueProvider();
    }
}
