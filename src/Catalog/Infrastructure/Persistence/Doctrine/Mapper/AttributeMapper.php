<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Attribute;
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
        $orm->version = $domain->getVersion()->value();
        $orm->createdBy = $domain->getCreatedBy()->value();
        $orm->updatedBy = $domain->getUpdatedBy()?->value();

        $this->mapTranslationsFromDomainToOrm($domain, $orm);

        return $orm;
    }

    /**
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

        $translations = $this->mapTranslationsFromOrmToDomain($orm);
        $options = $this->mapOptionsFromOrmToDomain($orm);

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
    private function mapOptionsFromOrmToDomain(OrmAttribute $orm): OptionCollection
    {
        $options = [];
        foreach ($orm->options as $ormOption) {
            $options[] = $this->attributeOptionMapper->toDomain($ormOption);
        }

        return OptionCollection::fromArray($options);
    }

    private function mapOptionsFromDomainToOrm(Attribute $domain, OrmAttribute $orm): void
    {
        $domainOptions = $domain->getOptions();
        $currentOrmOptions = $orm->options->toArray();

        foreach ($currentOrmOptions as $ormOption) {
            $stillExists = $domainOptions->getByUlid($ormOption->ulid);
            if (!$stillExists) {
                $orm->options->removeElement($ormOption);
            }
        }

        foreach ($domainOptions as $do) {
            $ormOption = array_find(
                $currentOrmOptions,
                fn (OrmAttributeOption $p) => $p->ulid === $do->getUlid()->value()
            );

            if (!$ormOption) {
                $ormOption = new OrmAttributeOption();
                $ormOption->attribute = $orm;

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

        foreach ($orm->translations as $ormTranslation) {
            if (null === $domainTranslations->get($ormTranslation->locale)) {
                $orm->translations->removeElement($ormTranslation);
            }
        }

        foreach ($domainTranslations as $locale => $translation) {
            $existing = $orm->translations->filter(fn (OrmAttributeTranslation $t) => $t->locale === $locale)->first();

            if ($existing) {
                $existing->name = $translation->name;
            } else {
                $newOrmTranslation = new OrmAttributeTranslation();
                $newOrmTranslation->attribute = $orm;
                $newOrmTranslation->locale = $locale;
                $newOrmTranslation->name = $translation->name;

                $orm->translations->add($newOrmTranslation);
            }
        }
    }
}
