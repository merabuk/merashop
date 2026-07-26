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
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;

final readonly class AttributeOptionMapper
{
    public function __construct(
        private AttributeOptionMetadataNormalizer $normalizer,
    ) {
    }

    /**
     * @throws EntityFieldMissingException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function toDomain(OrmAttributeOption $orm, Type $type): AttributeOption
    {
        $id = $orm->id ?? throw EntityFieldMissingException::forEntityId($orm::class);

        $translations = $this->mapTranslationsFromOrmToDomain($orm);

        return new AttributeOption(
            ulid: Ulid::fromString($orm->ulid ?? throw EntityFieldMissingException::forField(field: 'ulid', className: $orm::class)),
            code: Code::fromString($orm->code ?? throw EntityFieldMissingException::forField(field: 'code', className: $orm::class)),
            translations: $translations,
            isActive: ActiveFlag::fromBool($orm->isActive ?? throw EntityFieldMissingException::forField(field: 'isActive', className: $orm::class)),
            version: Version::fromInt($orm->version ?? throw EntityFieldMissingException::forField(field: 'version', className: $orm::class)),
            createdBy: AdminUlid::fromString($orm->createdBy ?? throw EntityFieldMissingException::forField(field: 'createdBy', className: $orm::class)),
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
     * @throws EntityFieldMissingException
     * @throws InvalidAttributeOptionValueException
     * @throws InvalidLocaleException
     */
    private function mapTranslationsFromOrmToDomain(OrmAttributeOption $orm): Translations
    {
        $translations = [];
        foreach ($orm->translations as $ormTranslation) {
            $translations[$ormTranslation->locale] = [
                'value' => $ormTranslation->value ?? throw EntityFieldMissingException::forField(field: 'value', className: $ormTranslation::class),
            ];
        }

        return Translations::fromArray($translations);
    }

    private function mapTranslationsFromDomainToOrm(AttributeOption $domain, OrmAttributeOption $orm): void
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
                $ormTranslation = new OrmAttributeOptionTranslation();
                $ormTranslation->option = $orm;
                $ormTranslation->locale = $locale;

                $orm->translations->add($ormTranslation);
            }

            $ormTranslation->value = $domainTranslation->value;
        }
    }
}
