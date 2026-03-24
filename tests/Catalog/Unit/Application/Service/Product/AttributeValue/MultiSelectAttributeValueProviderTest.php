<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use App\Catalog\Application\Service\Product\AttributeValue\MultiSelectAttributeValueProvider;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\AttributeOption\AttributeOptionNotFoundException;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Tests\Catalog\Support\AttributeMother;
use App\Tests\Catalog\Support\AttributeOptionMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MultiSelectAttributeValueProviderTest extends TestCase
{
    private const TypeEnum TYPE = TypeEnum::MultiSelect;

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(self::TYPE->value, $this->createProvider()::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $option1 = AttributeOptionMother::createWithData(ulid: '01KMDEC4Z9NSK4YPEW8NG5068T', id: 456);
        $option2 = AttributeOptionMother::createWithData(ulid: '01KMGY62KTY8BJ9J8NHMXHKF4P', id: 789);
        $options = [$option1, $option2];
        $attribute = AttributeMother::createWithData(type: self::TYPE, options: $options, id: 123);
        $data = self::getData(optionIds: [$option1->getId()->value(), $option2->getId()->value()]);

        $results = $this->createProvider()->handle($attribute, $data);

        self::assertCount(2, $results);

        foreach ($results as $i => $result) {
            self::assertInstanceOf(ProductAttributeValue::class, $result);
            self::assertNull($result->getValue());
            self::assertTrue($options[$i]->getId()->equals($result->getAttributeOptionId()));
        }
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
            'attribute' => AttributeMother::createWithData(type: TypeEnum::Integer),
            'data' => self::getData(),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'data type mismatch' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE),
            'data' => new IntegerAttributeValueData(value: 123),
            'expectedException' => InvalidArgumentException::class,
        ];
        yield 'unit option not found' => [
            'attribute' => AttributeMother::createWithData(type: self::TYPE, options: []),
            'data' => self::getData(),
            'expectedException' => AttributeOptionNotFoundException::class,
        ];
    }

    private static function getData(array $optionIds = [456, 789]): MultiSelectAttributeValueData
    {
        return new MultiSelectAttributeValueData(optionIds: $optionIds);
    }

    private function createProvider(): MultiSelectAttributeValueProvider
    {
        return new MultiSelectAttributeValueProvider();
    }
}
