<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Catalog\Domain\ValueObject\Category\Version;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategoryTranslation;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<Category, OrmCategory>
 */
final readonly class CategoryMapper implements MapperInterface
{
    use TypeCheckTrait;

    public function __construct(
        private ProxyReferenceProviderInterface $referenceProvider,
    ) {
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmCategory
    {
        $this->assertIsType(Category::class, $domain);
        /* @var Category $domain */

        $orm = new OrmCategory();

        $orm->ulid = $domain->getUlid()->value();
        $orm->version = $domain->getVersion()->value();
        $orm->createdBy = $domain->getCreatedBy()->value();

        $this->mapToExistingOrm($domain, $orm);

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
            ulid: Ulid::fromString($orm->ulid),
            parentId: null !== $orm->parent ? Id::fromInt($orm->parent->id) : null,
            path: Path::fromString($orm->path),
            slug: Slug::fromString($orm->slug),
            sortOrder: SortOrder::fromInt($orm->sortOrder),
            status: Status::fromEnum($orm->status),
            translations: Translations::fromArray($translations),
            version: Version::fromInt($orm->version),
            createdBy: AdminUlid::fromString($orm->createdBy),
            updatedBy: $orm->updatedBy ? AdminUlid::fromString($orm->updatedBy) : null,
            id: Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class)),
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(Category::class, $domain);
        $this->assertIsType(OrmCategory::class, $orm);
        /* @var Category $domain */
        /* @var OrmCategory $orm */

        $orm->path = $domain->getPath()->value();
        $orm->slug = $domain->getSlug()->value();
        $orm->sortOrder = $domain->getSortOrder()->value();
        $orm->status = $domain->getStatus()->value();
        $orm->updatedBy = $domain->getUpdatedBy()?->value();

        if (null !== $domain->getParentId()) {
            $orm->parent = $this->referenceProvider->getReference(
                className: OrmCategory::class,
                id: $domain->getParentId()->value()
            );
        } else {
            $orm->parent = null;
        }

        $this->mapTranslations($domain, $orm);
    }

    private function mapTranslations(Category $domain, OrmCategory $orm): void
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

        foreach ($domainTranslations as $locale => $translation) {
            $ormTranslation = $existingOrmTranslations[$locale] ?? null;

            if (!$ormTranslation) {
                $ormTranslation = new OrmCategoryTranslation();
                $ormTranslation->category = $orm;
                $ormTranslation->locale = $locale;

                $orm->translations->add($ormTranslation);
            }

            $ormTranslation->name = $translation->name;
            $ormTranslation->description = $translation->description;
        }
    }
}
