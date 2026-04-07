<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeStateException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeNameException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\OptionCollection;
use App\Catalog\Domain\ValueObject\Attribute\Translations;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Domain\ValueObject\Attribute\Version;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeOption;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeTranslation;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;
use Doctrine\ORM\PersistentCollection;

/**
 * @implements MapperInterface<Attribute, OrmAttribute>
 */
final readonly class AttributeMapper implements MapperInterface
{
    use TypeCheckTrait;

    public function __construct(
        private AttributeOptionMapper $attributeOptionMapper,
    ) {
    }

    /**
     * @throws AttributeStateException
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmAttribute
    {
        $this->assertIsType(Attribute::class, $domain);
        /** @var Attribute $domain */
        $orm = new OrmAttribute();

        $orm->ulid = $domain->getUlid()->value();
        $orm->code = $domain->getCode()->value();
        $orm->type = $domain->getType()->value();
        $orm->createdBy = $domain->getCreatedBy()->value();
        $orm->updatedBy = $domain->getUpdatedBy()?->value();

        $this->mapTranslationsFromDomainToOrm($domain, $orm);
        $this->mapOptionsFromDomainToOrm($domain, $orm);

        return $orm;
    }

    /**
     * @throws AttributeStateException
     * @throws EntityIdMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidLocaleException
     */
    public function fromDoctrineOrm(object $orm): Attribute
    {
        $this->assertIsType(OrmAttribute::class, $orm);
        /** @var OrmAttribute $orm */
        $id = Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        $type = Type::fromEnum($orm->type);

        $translations = $this->mapTranslationsFromOrmToDomain($orm);
        $options = $this->mapOptionsFromOrmToDomain($orm, $type);

        return new Attribute(
            ulid: Ulid::fromString($orm->ulid),
            code: Code::fromString($orm->code),
            type: Type::fromEnum($orm->type),
            translations: $translations,
            version: Version::fromInt($orm->version),
            createdBy: AdminUlid::fromString($orm->createdBy),
            options: $options,
            updatedBy: $orm->updatedBy ? AdminUlid::fromString($orm->updatedBy) : null,
            id: $id,
        );
    }

    /**
     * @throws AttributeStateException
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(Attribute::class, $domain);
        $this->assertIsType(OrmAttribute::class, $orm);
        /* @var Attribute $domain */
        /* @var OrmAttribute $orm */

        $orm->code = $domain->getCode()->value();
        $orm->type = $domain->getType()->value();
        $orm->updatedBy = $domain->getUpdatedBy()?->value();

        $this->mapTranslationsFromDomainToOrm($domain, $orm);
        $this->mapOptionsFromDomainToOrm($domain, $orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function mapOptionsFromOrmToDomain(OrmAttribute $orm, Type $type): OptionCollection
    {
        $optionsCollection = $orm->options;

        if ($optionsCollection instanceof PersistentCollection && !$optionsCollection->isInitialized()) {
            return OptionCollection::uninitialized();
        }

        $options = [];
        foreach ($optionsCollection as $ormOption) {
            $options[] = $this->attributeOptionMapper->toDomain($ormOption, $type);
        }

        return OptionCollection::fromArray($options);
    }

    /**
     * @throws AttributeStateException
     */
    private function mapOptionsFromDomainToOrm(Attribute $domain, OrmAttribute $orm): void
    {
        $domainOptions = $domain->getOptions();

        if (false === $domainOptions->isInitialized()) {
            throw AttributeStateException::becauseCanNotSaveAttributeWithUninitializedOptions();
        }

        $existingOrmOptions = [];

        foreach ($orm->options as $o) {
            $existingOrmOptions[$o->ulid] = $o;
        }

        foreach ($domainOptions as $do) {
            $ormOption = $existingOrmOptions[$do->getUlid()->value()] ?? null;

            if (!$ormOption) {
                $ormOption = new OrmAttributeOption();
                $ormOption->attribute = $orm;
                $ormOption->ulid = $do->getUlid()->value();
                $ormOption->createdBy = $do->getCreatedBy()->value();

                $orm->options->add($ormOption);
            }

            $this->attributeOptionMapper->mapToExistingOrm($do, $ormOption);
        }
    }

    /**
     * @throws InvalidAttributeNameException
     * @throws InvalidLocaleException
     */
    private function mapTranslationsFromOrmToDomain(OrmAttribute $orm): Translations
    {
        $translations = [];
        foreach ($orm->translations as $ormTranslation) {
            $translations[$ormTranslation->locale] = ['name' => $ormTranslation->name];
        }

        return Translations::fromArray($translations);
    }

    private function mapTranslationsFromDomainToOrm(Attribute $domain, OrmAttribute $orm): void
    {
        $domainTranslations = $domain->getTranslations();

        $existingOrmTranslations = [];
        foreach ($orm->translations as $t) {
            $existingOrmTranslations[$t->locale] = $t;
        }

        foreach ($existingOrmTranslations as $locale => $ormTranslation) {
            if (!$domainTranslations->has($locale)) {
                $orm->translations->removeElement($ormTranslation);
            }
        }

        foreach ($domainTranslations as $locale => $domainTranslation) {
            $ormTranslation = $existingOrmTranslations[$locale] ?? null;

            if (!$ormTranslation) {
                $ormTranslation = new OrmAttributeTranslation();
                $ormTranslation->attribute = $orm;
                $ormTranslation->locale = $locale;

                $orm->translations->add($ormTranslation);
            }

            $ormTranslation->name = $domainTranslation->name;
        }
    }
}
