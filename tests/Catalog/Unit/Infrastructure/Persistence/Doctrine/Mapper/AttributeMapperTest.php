<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Domain\ValueObject\Attribute\Version;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeTranslation;
use App\Catalog\Infrastructure\Persistence\Doctrine\Mapper\AttributeMapper;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AttributeMapperTest extends TestCase
{
    private AttributeMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new AttributeMapper();
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function testToDoctrineOrm(): void
    {
        $domain = $this->makeDomainEntity();

        $orm = $this->mapper->toDoctrineOrm($domain);

        self::assertNull($orm->id);
        self::assertSame($domain->getUlid()->value(), $orm->ulid);
        self::assertSame($domain->getCode()->value(), $orm->code);
        self::assertSame($domain->getType()->value(), $orm->type);
        foreach (LocaleEnum::cases() as $locale) {
            $translationVO = $domain->getTranslations()->get($locale->value);
            $translationOrm = $orm->translations->filter(fn (OrmAttributeTranslation $t) => $t->locale === $locale->value)->first();

            self::assertNotNull($translationVO, sprintf('Translation for %s locale not found', $locale->value));
            self::assertNotNull($translationOrm, sprintf('Translation for %s locale not mapped', $locale->value));
            self::assertSame($translationVO->locale->value(), $translationOrm->locale);
            self::assertSame($translationVO->name, $translationOrm->name);
        }
        self::assertSame($domain->getVersion()->value(), $orm->version);
        self::assertSame($domain->getCreatedBy()->value(), $orm->createdBy);
        self::assertSame($domain->getUpdatedBy()->value(), $orm->updatedBy);

    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidLocaleException
     */
    public function testFromDoctrineOrm(): void
    {
        $orm = $this->makeOrmEntity();

        $domain = $this->mapper->fromDoctrineOrm($orm);

        self::assertSame($orm->id, $domain->getId()?->value());
        self::assertSame($orm->ulid, $domain->getUlid()->value());
        self::assertSame($orm->code, $domain->getCode()->value());
        self::assertSame($orm->type, $domain->getType()->value());
        foreach (LocaleEnum::cases() as $locale) {
            $translationOrm = $orm->translations->filter(fn (OrmAttributeTranslation $t) => $t->locale === $locale->value)->first();
            $translationVO = $domain->getTranslations()->get($locale->value);

            self::assertNotNull($translationOrm, sprintf('Translation for %s locale not found', $locale->value));
            self::assertNotNull($translationVO, sprintf('Translation for %s locale not mapped', $locale->value));
            self::assertSame($translationOrm->locale, $translationVO->locale->value());
            self::assertSame($translationOrm->name, $translationVO->name);
        }
        self::assertSame($orm->version, $domain->getVersion()->value());
        self::assertSame($orm->createdBy, $domain->getCreatedBy()->value());
        self::assertSame($orm->updatedBy, $domain->getUpdatedBy()->value());
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function testMapToExistingOrm(): void
    {
        $domain = $this->makeDomainEntity();
        $orm = new OrmAttribute();

        // imitate old data
        $orm->code = 'old-test-code';
        $orm->type = TypeEnum::Boolean;
        $orm->updatedBy = '01KHVRCC1ST3154XR7WD58P6JY';

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertSame($domain->getCode()->value(), $orm->code);
        self::assertSame($domain->getType()->value(), $orm->type);
        self::assertSame($domain->getUpdatedBy()->value(), $orm->updatedBy);

        // no editable fields
        self::assertNull($orm->id);
        self::assertNull($orm->ulid);
        self::assertNull($orm->version);
        self::assertNull($orm->createdBy);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function testThrowExceptionOnInvalidEntity(): void
    {
        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->fromDoctrineOrm(new stdClass());

        $this->expectException(IncompatibleMappedEntityException::class);
        $this->mapper->toDoctrineOrm(new stdClass());
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function testThrowExceptionOnInvalidId(): void
    {
        $this->expectException(EntityIdMissingException::class);
        $this->mapper->fromDoctrineOrm(new OrmAttribute());
    }

    /**
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function makeDomainEntity(): Attribute
    {
        $fakeId = 123;
        $ulid = '01KHVRCC1RJ542BED4NYXHJ2EV';
        $code = 'test-code';
        $type = TypeEnum::String;
        $translations = [];
        foreach (LocaleEnum::cases() as $locale) {
            $translations[$locale->value] = [
                'name' => $locale->value.' test name',
            ];
        }
        $version = 1;
        $createdBy = '01KHVRCC1ST3154XR7WD58P6JY';
        $updatedBy = '01KHVRCC1Z9S7G603HEPK9MGEZ';

        return new Attribute(
            id: Id::fromInt($fakeId),
            ulid: Ulid::fromString($ulid),
            code: Code::fromString($code),
            type: Type::fromEnum($type),
            translations: Translations::fromArray($translations),
            version: Version::fromInt($version),
            createdBy: AdminUlid::fromString($createdBy),
            updatedBy: AdminUlid::fromString($updatedBy),
        );
    }

    private function makeOrmEntity(): OrmAttribute
    {
        $orm = new OrmAttribute();

        $fakeId = 123;
        $ulid = '01KHVRCC1RJ542BED4NYXHJ2EV';
        $code = 'test-code';
        $type = TypeEnum::String;
        $translations = [];
        foreach (LocaleEnum::cases() as $locale) {
            $translation = new OrmAttributeTranslation();
            $translation->locale = $locale->value;
            $translation->name = $locale->value.' test name';
            $translation->attribute = $orm;

            $translations[] = $translation;
        }
        $version = 1;
        $createdBy = '01KHVRCC1ST3154XR7WD58P6JY';
        $updatedBy = '01KHVRCC1Z9S7G603HEPK9MGEZ';

        $orm->setId($fakeId);
        $orm->ulid = $ulid;
        $orm->code = $code;
        $orm->type = $type;
        $orm->translations = new ArrayCollection($translations);
        $orm->version = $version;
        $orm->createdBy = $createdBy;
        $orm->updatedBy = $updatedBy;

        return $orm;
    }
}
