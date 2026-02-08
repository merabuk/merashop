<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeCodeException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeIdException;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeUlidException;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttributeTranslation;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<Attribute, OrmAttribute>
 */
class AttributeMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmAttribute
    {
        $this->assertIsType(Attribute::class, $domain);
        /** @var Attribute $domain */

        $orm = new OrmAttribute();
        $this->mapToExistingOrm($domain, $orm);

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidAttributeIdException
     * @throws InvalidAttributeUlidException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidAttributeCodeException
     */
    public function fromDoctrineOrm(object $orm): Attribute
    {
        $this->assertIsType(OrmAttribute::class, $orm);
        /** @var OrmAttribute $orm */

        $translations = [];
        foreach ($orm->translations as $translation) {
            $translations[$translation->locale] = $translation->name;
        }

        return new Attribute(
            id: Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class)),
            ulid: Ulid::fromString($orm->ulid),
            code: Code::fromString($orm->code),
            type: $orm->type,
            translations: $translations
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(Attribute::class, $domain);
        $this->assertIsType(OrmAttribute::class, $orm);
        /** @var Attribute $domain */
        /** @var OrmAttribute $orm */

        $orm->ulid = $domain->getUlid()->value();
        $orm->code = $domain->getCode()->value();
        $orm->type = $domain->getType();

        // Map translations
        $currentTranslations = [];
        foreach ($orm->translations as $translation) {
            $currentTranslations[$translation->locale] = $translation;
        }

        foreach ($domain->getTranslations() as $locale => $name) {
            if (isset($currentTranslations[$locale])) {
                $currentTranslations[$locale]->name = $name;
                unset($currentTranslations[$locale]);
            } else {
                $translation = new OrmAttributeTranslation();
                $translation->attribute = $orm;
                $translation->locale = $locale;
                $translation->name = $name;
                $orm->translations->add($translation);
            }
        }

        foreach ($currentTranslations as $translation) {
            $orm->translations->removeElement($translation);
        }
    }
}
