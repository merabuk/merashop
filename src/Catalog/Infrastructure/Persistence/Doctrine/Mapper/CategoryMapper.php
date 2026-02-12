<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategoryTranslation;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

class CategoryMapper
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain, ?object $ormParent): OrmCategory
    {
        $orm = new OrmCategory();
        $this->mapToExistingOrm($domain, $orm, $ormParent);

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function fromDoctrineOrm(object $orm): Category
    {
        $this->assertIsType(OrmCategory::class, $orm);
        /** @var OrmCategory $orm */
        $translations = [];
        foreach ($orm->translations as $ormTranslation) {
            $translations[$ormTranslation->locale] = [
                'name' => $ormTranslation->name,
                'description' => $ormTranslation->description,
            ];
        }

        return new Category(
            id: Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class)),
            ulid: Ulid::fromString($orm->ulid),
            parentId: null !== $orm->parent ? Id::fromInt($orm->parent->id) : null,
            path: Path::fromString($orm->path),
            slug: Slug::fromString($orm->slug),
            sortOrder: SortOrder::fromInt($orm->sortOrder),
            status: Status::fromEnum($orm->status),
            translations: Translations::fromArray($translations)
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm, ?object $ormParent): void
    {
        $this->assertIsType(Category::class, $domain);
        $this->assertIsType(OrmCategory::class, $orm);
        if (null !== $ormParent) {
            $this->assertIsType(OrmCategory::class, $ormParent);
        }
        /* @var Category $domain */
        /* @var OrmCategory $orm */
        /* @var ?OrmCategory $ormParent */

        $orm->ulid = $domain->getUlid()->value();
        $orm->path = $domain->getPath()->value();
        $orm->slug = $domain->getSlug()->value();
        $orm->sortOrder = $domain->getSortOrder()->value();
        $orm->status = $domain->getStatus()->value();
        $orm->parent = $ormParent;

        $this->mapTranslations($domain, $orm);
    }

    private function mapTranslations(Category $domain, OrmCategory $orm): void
    {
        $domainTranslations = $domain->getTranslations();

        foreach ($orm->translations as $ormTranslation) {
            if (null === $domainTranslations->get($ormTranslation->locale)) {
                $orm->translations->removeElement($ormTranslation);
            }
        }

        foreach ($domainTranslations as $locale => $translation) {
            $existing = $orm->translations->filter(fn (OrmCategoryTranslation $t) => $t->locale === $locale)->first();

            if ($existing) {
                $existing->name = $translation->name;
                $existing->description = $translation->description;
            } else {
                $newOrmTranslation = new OrmCategoryTranslation();
                $newOrmTranslation->category = $orm;
                $newOrmTranslation->locale = $locale;
                $newOrmTranslation->name = $translation->name;
                $newOrmTranslation->description = $translation->description;

                $orm->translations->add($newOrmTranslation);
            }
        }
    }
}
