<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionValueException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Catalog\Domain\ValueObject\AttributeOption\ActiveFlag;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Catalog\Domain\ValueObject\AttributeOption\Id;
use App\Catalog\Domain\ValueObject\AttributeOption\Translations;
use App\Catalog\Domain\ValueObject\AttributeOption\Ulid;
use App\Catalog\Domain\ValueObject\AttributeOption\Version;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeOption;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeOptionTranslation;
use App\Catalog\Infrastructure\Persistence\Doctrine\Normalizer\AttributeOptionMetadataNormalizer;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class AttributeOptionMapper
{
    public function __construct(
        private AttributeOptionMetadataNormalizer $normalizer,
    ) {
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function toDomain(OrmAttributeOption $orm, Type $type): AttributeOption
    {
        $id = $orm->id ?? throw EntityIdMissingException::forEntity($orm::class);

        $translations = $this->mapTranslationsFromOrmToDomain($orm);

        return new AttributeOption(
            ulid: Ulid::fromString($orm->ulid),
            code: Code::fromString($orm->code),
            translations: $translations,
            isActive: ActiveFlag::fromBool($orm->isActive),
            version: Version::fromInt($orm->version),
            createdBy: AdminUlid::fromString($orm->createdBy),
            metadata: $this->normalizer->denormalize($type, $orm->valueJson),
            updatedBy: $orm->updatedBy ? AdminUlid::fromString($orm->updatedBy) : null,
            id: Id::fromInt($id),
        );
    }

    public function mapToExistingOrm(AttributeOption $domain, OrmAttributeOption $orm): void
    {
        $orm->code = $domain->getCode()->value();
        $orm->isActive = $domain->isActive()->value();
        $orm->valueJson = $this->normalizer->normalize($domain->getMetadata());
        $orm->updatedBy = $domain->getUpdatedBy()?->value();

        $this->mapTranslationsFromDomainToOrm($domain, $orm);
    }

    /**
     * @throws InvalidAttributeOptionValueException
     * @throws InvalidLocaleException
     */
    private function mapTranslationsFromOrmToDomain(OrmAttributeOption $orm): Translations
    {
        $translations = [];
        foreach ($orm->translations as $ormTranslation) {
            $translations[$ormTranslation->locale] = [
                'value' => $ormTranslation->value,
            ];
        }

        return Translations::fromArray($translations);
    }

    private function mapTranslationsFromDomainToOrm(AttributeOption $domain, OrmAttributeOption $orm): void
    {
        $domainTranslations = $domain->getTranslations();

        foreach ($orm->translations as $ormTranslation) {
            if (null === $domainTranslations->get($ormTranslation->locale)) {
                $orm->translations->removeElement($ormTranslation);
            }
        }

        foreach ($domainTranslations as $locale => $translation) {
            $existing = $orm->translations->filter(fn (OrmAttributeOptionTranslation $t) => $t->locale === $locale)->first();

            if ($existing) {
                $existing->value = $translation->value;
            } else {
                $ormTranslation = new OrmAttributeOptionTranslation();
                $ormTranslation->option = $orm;
                $ormTranslation->locale = $locale;
                $ormTranslation->value = $translation->value;

                $orm->translations->add($ormTranslation);
            }
        }
    }
}
