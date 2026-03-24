<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\BooleanAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Application\Service\Product\AttributeValue\IntegerAttributeValueProvider;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\IntegerValue;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IntegerAttributeValueProviderTest extends TestCase
{
    private const TypeEnum TYPE = TypeEnum::Integer;

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(self::TYPE->value, $this->createProvider()::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $attribute = AttributeMother::createWithData(type: self::TYPE, id: 123);
        $data = self::getData();

        $results = $this->createProvider()->handle($attribute, $data);

        self::assertCount(1, $results);
        $result = $results[0];
        self::assertInstanceOf(ProductAttributeValue::class, $result);
        $value = $result->getValue();
        self::assertInstanceOf(IntegerValue::class, $value);
        self::assertSame($data->value, $value->value());
    }

    #[DataProvider('invalidDataProvider')]
    public function testThrowExceptionsWhenHasInvalidData(
        Attribute $attribute,
        AttributeValueDataInterface $data,
        string $expectedException,
    ): void {
        $this->expectException($expectedException);

        $this->createProvider()->handle($attribute, $data);
    }

    public static function invalidDataProvider(): iterable
    {
        yield 'attribute type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Boolean),
            'data' => self::getData(),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'data type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE),
            'data' => new BooleanAttributeValueData(value: false),
            'expectedException' => InvalidArgumentException::class,
        ];
    }

    private static function getData(int $value = 12345): IntegerAttributeValueData
    {
        return new IntegerAttributeValueData(value: $value);
    }

    private function createProvider(): IntegerAttributeValueProvider
    {
        return new IntegerAttributeValueProvider();
    }
}
