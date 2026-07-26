<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\DimensionMetadata;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeOption;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeOptionTranslation;
use App\Catalog\Infrastructure\Persistence\Doctrine\Mapper\AttributeOptionMapper;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\AttributeOptionMetadataNormalizer;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Tests\Catalog\Support\AttributeOptionMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\Attributes\DataProvider;

final class AttributeOptionMapperTest extends BaseUnitTest
{
    private AttributeOptionMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new AttributeOptionMapper(
            normalizer: new AttributeOptionMetadataNormalizer()
        );
    }

    #[DataProvider('toDomainProvider')]
    public function testToDomain(TypeEnum $enum, ?array $metadata): void
    {
        $type = Type::fromEnum($enum);

        $orm = new OrmAttributeOption();
        $orm->setId(123);
        $orm->ulid = AttributeOptionMother::DEFAULT_ULID;
        $orm->code = 'option-code';
        $orm->isActive = true;
        $orm->valueJson = $metadata;
        $orm->version = 1;
        $orm->createdBy = AttributeOptionMother::DEFAULT_ADMIN_ULID;
        $orm->updatedBy = AttributeOptionMother::DEFAULT_ADMIN_ULID;

        $translation = new OrmAttributeOptionTranslation();
        $translation->locale = 'uk';
        $translation->value = 'uk-option-value';

        $orm->translations->add($translation);

        $domain = $this->mapper->toDomain($orm, $type);

        self::assertSame($orm->id, $domain->getId()->value());
        self::assertSame($orm->ulid, $domain->getUlid()->value());
        self::assertSame($orm->code, $domain->getCode()->value());
        self::assertCount($orm->translations->count(), $domain->getTranslations());
        foreach ($orm->translations as $translation) {
            $actualTranslation = $domain->getTranslations()->get($translation->locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->value, $actualTranslation->value);
        }
        self::assertSame($orm->isActive, $domain->isActive()->value());
        self::assertSame($orm->version, $domain->getVersion()->value());
        self::assertSame($orm->createdBy, $domain->getCreatedBy()->value());
        if ($type->is(TypeEnum::Dimension)) {
            self::assertNotNull($domain->getMetadata());
            self::assertInstanceOf(DimensionMetadata::class, $domain->getMetadata());
            self::assertSame($orm->valueJson['base_ratio'], $domain->getMetadata()->getBaseRatio());
        } else {
            self::assertNull($domain->getMetadata());
        }
        self::assertSame($orm->updatedBy, $domain->getUpdatedBy()?->value());
    }

    public static function toDomainProvider(): iterable
    {
        yield 'without metadata' => [TypeEnum::String, null];
        yield 'with metadata' => [TypeEnum::Dimension, ['base_ratio' => 0.01]];
    }

    public function testMapToDomainThrowsExceptionWhenOrmMissingId(): void
    {
        $orm = new OrmAttributeOption();

        $this->expectException(EntityFieldMissingException::class);

        $this->mapper->toDomain($orm, Type::fromEnum(TypeEnum::String));
    }

    #[DataProvider('mapToExistingOrmProvider')]
    public function testMapToExistingOrm(
        TypeEnum $enum,
        ?array $oldOrmMetadata,
        mixed $domainMetadata,
    ): void {
        $domain = AttributeOptionMother::createWithData(attributeType: $enum, metadata: $domainMetadata, id: 123);

        $orm = new OrmAttributeOption();
        $orm->code = 'old-option-code';
        $orm->isActive = false;
        $orm->valueJson = $oldOrmMetadata;
        $orm->updatedBy = 'old-admin-ulid';

        foreach (['xx', 'en'] as $locale) {
            $translation = new OrmAttributeOptionTranslation();
            $translation->locale = $locale;
            $translation->value = "Old {$locale} option value";

            $orm->translations->add($translation);
        }

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertSame($domain->getCode()->value(), $orm->code);
        self::assertCount($domain->getTranslations()->count(), $orm->translations);
        foreach ($domain->getTranslations() as $locale => $translation) {
            $ormTranslation = $orm->translations->filter(
                fn (OrmAttributeOptionTranslation $t) => $t->locale === $locale
            )->first();

            self::assertNotNull($ormTranslation, sprintf('Translation for %s locale not found in ORM', $locale));
            self::assertSame($translation->value, $ormTranslation->value);
        }
        self::assertSame($domain->isActive()->value(), $orm->isActive);
        self::assertSame(match ($enum) {
            TypeEnum::Dimension => ['base_ratio' => $domain->getMetadata()?->getBaseRatio()],
            default => null,
        }, $orm->valueJson);
        self::assertSame($domain->getUpdatedBy()?->value(), $orm->updatedBy);
        self::assertNull($orm->id);
        self::assertNull($orm->ulid);
        self::assertNull($orm->version);
        self::assertNull($orm->createdBy);
    }

    public static function mapToExistingOrmProvider(): iterable
    {
        yield 'without metadata' => [TypeEnum::String, null, null];
        yield 'with metadata' => [TypeEnum::Dimension, ['base_ratio' => 0.01], 0.1];
        yield 'when metadata was corrupted' => [TypeEnum::Dimension, null, DimensionMetadata::BASE_RATIO];
    }
}
