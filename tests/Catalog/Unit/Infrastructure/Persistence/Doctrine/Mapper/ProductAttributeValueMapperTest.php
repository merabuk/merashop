<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\Attribute\TypeEnum as AttributeTypeEnum;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeColorValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeDateValueException;
use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeUrlValueException;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeOption;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductAttributeValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Mapper\ProductAttributeValueMapper;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\ProductAttributeValueNormalizer;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface;
use App\Tests\Catalog\Support\ProductAttributeValueMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductAttributeValueMapperTest extends TestCase
{
    private ProxyReferenceProviderInterface $referenceProvider;
    private ProductAttributeValueNormalizer $normalizer;
    private ProductAttributeValueMapper $mapper;

    protected function setUp(): void
    {
        $this->referenceProvider = $this->createMock(ProxyReferenceProviderInterface::class);
        $this->normalizer = new ProductAttributeValueNormalizer();
        $this->mapper = new ProductAttributeValueMapper(
            referenceProvider: $this->referenceProvider,
            normalizer: $this->normalizer,
        );
    }

    #[DataProvider('validAttributeValueProvider')]
    public function testToDomain(
        AttributeTypeEnum $attributeType,
        ?int $optionId,
    ): void {
        $forValue = ProductAttributeValueMother::createWithData(
            attributeId: 321,
            attributeType: $attributeType,
            optionId: $optionId,
        );

        $attribute = new OrmAttribute();
        $attribute->setId(321);
        $attribute->type = $attributeType;

        $orm = new OrmProductAttributeValue();
        $orm->setId(123);
        $orm->attribute = $attribute;
        $orm->valueJson = $this->normalizer->normalize($forValue->getValue());
        $orm->version = 1;
        $orm->createdBy = ProductAttributeValueMother::DEFAULT_ADMIN_ULID;

        if (null !== $optionId) {
            $option = new OrmAttributeOption();
            $option->setId($optionId);

            $orm->option = $option;
        }

        $domain = $this->mapper->toDomain($orm);

        self::assertSame($orm->id, $domain->getId()->value());
        self::assertSame($orm->attribute->id, $domain->getAttributeId()->value());
        self::assertSame($orm->valueJson, $this->normalizer->normalize($domain->getValue()));
    }

    #[DataProvider('validAttributeValueProvider')]
    public function testMapToExistingOrm(
        AttributeTypeEnum $attributeType,
        ?int $optionId = null,
    ): void {
        $domain = ProductAttributeValueMother::createWithData(
            attributeId: 321,
            attributeType: $attributeType,
            optionId: $optionId,
        );

        $orm = new OrmProductAttributeValue();

        if (null !== $optionId) {
            $option = new OrmAttributeOption();
            $option->setId($optionId);

            $this->referenceProvider->expects(self::once())
                ->method('getReference')
                ->with(
                    self::equalTo(OrmAttributeOption::class),
                    self::equalTo($optionId),
                )
                ->willReturn($option);
        }

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertNull($orm->id);
        self::assertSame($this->normalizer->normalize($domain->getValue()), $orm->valueJson);
    }

    public static function validAttributeValueProvider(): iterable
    {
        foreach (AttributeTypeEnum::cases() as $type) {
            if (TypeEnum::Image === $type) {
                continue;
            }

            yield $type->name => [
                'attributeType' => $type,
                'optionId' => $type->hasOptions() ? 123 : null,
            ];
        }
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
        string $expectedException = InvalidArgumentException::class,
        ?array $value = null,
        ?int $optionId = null,
    ): void {
        $attribute = new OrmAttribute();
        $attribute->setId(321);
        $attribute->type = $attributeType;

        $orm = new OrmProductAttributeValue();
        $orm->setId(123);
        $orm->attribute = $attribute;
        $orm->valueJson = $value;
        $orm->version = 1;
        $orm->createdBy = ProductAttributeValueMother::DEFAULT_ADMIN_ULID;

        if (null !== $optionId) {
            $option = new OrmAttributeOption();
            $option->setId($optionId);

            $orm->option = $option;
        }

        $this->expectException($expectedException);

        $this->mapper->toDomain($orm);
    }

    public static function invalidMapToDomainProvider(): iterable
    {
        yield 'attribute type is null' => [
            'attributeType' => null,
        ];
        yield 'attribute type string and value not' => [
            'attributeType' => AttributeTypeEnum::String,
            'value' => ['translations' => 123456789],
        ];
        yield 'attribute type text and value not' => [
            'attributeType' => AttributeTypeEnum::Text,
            'value' => ['translations' => 123456789],
        ];
        yield 'attribute type integer and value not' => [
            'attributeType' => AttributeTypeEnum::Integer,
            'value' => ['value' => 'invalid value'],
        ];
        yield 'attribute type float and value not' => [
            'attributeType' => AttributeTypeEnum::Float,
            'value' => ['value' => 'invalid value'],
        ];
        yield 'attribute type boolean and value not' => [
            'attributeType' => AttributeTypeEnum::Boolean,
            'value' => ['value' => 'true'],
        ];
        yield 'attribute type select and option not' => [
            'attributeType' => AttributeTypeEnum::Select,
        ];
        yield 'attribute type multiselect and option not' => [
            'attributeType' => AttributeTypeEnum::MultiSelect,
        ];
        yield 'attribute type color and value not' => [
            'attributeType' => AttributeTypeEnum::Color,
            'expectedException' => InvalidProductAttributeColorValueException::class,
            'value' => ['value' => ''],
        ];
        yield 'attribute type date and value not' => [
            'attributeType' => AttributeTypeEnum::Date,
            'expectedException' => InvalidProductAttributeDateValueException::class,
            'value' => ['value' => ''],
        ];
        yield 'attribute type url and value not' => [
            'attributeType' => AttributeTypeEnum::Url,
            'expectedException' => InvalidProductAttributeUrlValueException::class,
            'value' => ['value' => ''],
        ];
        yield 'attribute type dimension and value not' => [
            'attributeType' => AttributeTypeEnum::Dimension,
            'value' => ['magnitude' => ''],
            'optionId' => 123,
        ];
        yield 'attribute type dimension and option not' => [
            'attributeType' => AttributeTypeEnum::Dimension,
            'value' => ['magnitude' => 1000.0],
        ];
        yield 'unsupported attribute type' => [
            'attributeType' => AttributeTypeEnum::Image,
            'value' => [],
        ];
    }
}
