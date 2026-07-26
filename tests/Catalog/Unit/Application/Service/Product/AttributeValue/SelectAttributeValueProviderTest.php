<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\SelectAttributeValueData;
use App\Catalog\Application\Service\Product\AttributeValue\SelectAttributeValueProvider;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class SelectAttributeValueProviderTest extends BaseUnitTest
{
    private const TypeEnum TYPE = TypeEnum::Select;

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(self::TYPE->value, $this->createProvider()::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $option = AttributeOptionMother::createWithData(id: 456);
        $attribute = AttributeMother::createWithData(type: self::TYPE, options: [$option], id: 123);
        $data = self::getData(optionId: $option->getId()->value());

        $results = $this->createProvider()->handle($attribute, $data, $attribute->getCreatedBy());

        self::assertCount(1, $results);
        $result = $results[0];
        self::assertInstanceOf(ProductAttributeValue::class, $result);
        self::assertNull($result->getValue());
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
        $optionId1 = 456;

        yield 'attribute type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Integer, id: 123),
            'data' => self::getData(),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'data type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE, options: [
                AttributeOptionMother::createWithData(id: $optionId1),
            ], id: 123),
            'data' => new IntegerAttributeValueData(value: 123),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'unit option not found' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE, options: [
                AttributeOptionMother::createWithData(id: 789),
            ], id: 123),
            'data' => self::getData($optionId1),
            'expectedException' => AttributeOptionNotFoundException::class,
        ];
    }

    private static function getData(int $optionId = 456): SelectAttributeValueData
    {
        return new SelectAttributeValueData(optionId: $optionId);
    }

    private function createProvider(): SelectAttributeValueProvider
    {
        return new SelectAttributeValueProvider();
    }
}
