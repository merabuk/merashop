<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\StringAttributeValueData;
use App\Catalog\Application\Service\Product\AttributeValue\StringAttributeValueProvider;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\LocalizedStringValue;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Tests\Catalog\Support\AttributeMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringAttributeValueProviderTest extends TestCase
{
    private const TypeEnum TYPE = TypeEnum::String;

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
        self::assertInstanceOf(LocalizedStringValue::class, $value);
        self::assertSame($data->translations, $value->value());
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

    private static function getData(): StringAttributeValueData
    {
        return new StringAttributeValueData(translations: [
            'en' => 'Test string value',
            'uk' => 'Тестове строкове значення',
        ]);
    }

    private function createProvider(): StringAttributeValueProvider
    {
        return new StringAttributeValueProvider();
    }
}
