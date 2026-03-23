<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Mapper\ProductAttributeValueMapper;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductAttributeValueMapperTest extends TestCase
{
    private ProductAttributeValueMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ProductAttributeValueMapper();
    }

    #[DataProvider('validAttributeValueProvider')]
    public function testToDomain(
        AttributeTypeEnum $attributeType,
        mixed $value,
    ): void {
        $attribute = new OrmAttribute();
        $attribute->setId(321);
        $attribute->type = $attributeType;

        $orm = new OrmProductAttributeValue();
        $orm->setId(123);
        $orm->attribute = $attribute;
        $orm->valueJson = ['value' => $value];

        $domain = $this->mapper->toDomain($orm);

        self::assertSame($orm->id, $domain->getId()->value());
        self::assertSame($orm->attribute->id, $domain->getAttributeId()->value());
        self::assertSame($orm->valueJson['value'], $domain->getValue()->value());
    }

    #[DataProvider('validAttributeValueProvider')]
    public function testMapToExistingOrm(
        AttributeTypeEnum $attributeType,
        mixed $value,
    ): void {
        $domain = ProductAttributeValueMother::createWithData(
            attributeId: 321,
            attributeType: $attributeType,
            value: $value,
        );

        $orm = new OrmProductAttributeValue();

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertNull($orm->id);
        self::assertSame($domain->getValue()->value(), $orm->valueJson['value']);
    }

    public static function validAttributeValueProvider(): iterable
    {
        yield 'string value' => [
            'attributeType' => AttributeTypeEnum::String,
            'value' => 'string value',
        ];
        yield 'integer value' => [
            'attributeType' => AttributeTypeEnum::Integer,
            'value' => 123456789,
        ];
        yield 'boolean value' => [
            'attributeType' => AttributeTypeEnum::Boolean,
            'value' => false,
        ];
        yield 'array value' => [
            'attributeType' => AttributeTypeEnum::Select,
            'value' => ['Option 1', 'Option 2'],
        ];
    }

    public function testToDomainThrowsExceptionWhenOrmMissingId(): void
    {
        $orm = new OrmProductAttributeValue();

        $this->expectException(EntityIdMissingException::class);

        $this->mapper->toDomain($orm);
    }

    #[DataProvider('invalidMapToDomainProvider')]
    public function testToDomainThrowsExceptionOnInvalidData(
        ?AttributeTypeEnum $attributeType,
        mixed $value,
    ): void {
        $attribute = new OrmAttribute();
        $attribute->setId(321);
        $attribute->type = $attributeType;

        $orm = new OrmProductAttributeValue();
        $orm->setId(123);
        $orm->attribute = $attribute;
        $orm->valueJson = ['value' => $value];

        $this->expectException(InvalidArgumentException::class);

        $this->mapper->toDomain($orm);
    }

    public static function invalidMapToDomainProvider(): iterable
    {
        yield 'attribute type is null' => [
            'attributeType' => null,
            'value' => 'string value',
        ];
        yield 'raw value is null' => [
            'attributeType' => AttributeTypeEnum::String,
            'value' => null,
        ];
        yield 'attribute type string and value not' => [
            'attributeType' => AttributeTypeEnum::String,
            'value' => 123456789,
        ];
        yield 'attribute type int and value not' => [
            'attributeType' => AttributeTypeEnum::Integer,
            'value' => 'invalid value',
        ];
        yield 'attribute type bool and value not' => [
            'attributeType' => AttributeTypeEnum::Boolean,
            'value' => 'true',
        ];
        yield 'attribute type array and value not' => [
            'attributeType' => AttributeTypeEnum::Select,
            'value' => 'array',
        ];
    }
}
