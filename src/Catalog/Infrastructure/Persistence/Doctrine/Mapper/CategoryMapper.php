<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategoryTranslation;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

/**
 * @implements MapperInterface<Category, OrmCategory>
 */
class CategoryMapper implements MapperInterface
{
    use TypeCheckTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     */
    public function toDoctrineOrm(object $domain): OrmCategory
    {
        $this->assertIsType(Category::class, $domain);
        /** @var Category $domain */

        $orm = new OrmCategory();
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
        foreach ($orm->translations as $translation) {
            $translations[$translation->locale] = [
                'name' => $translation->name,
                'description' => $translation->description,
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
     * @throws ORMException
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(Category::class, $domain);
        $this->assertIsType(OrmCategory::class, $orm);
        /** @var Category $domain */
        /** @var OrmCategory $orm */

        $orm->ulid = $domain->getUlid()->value();
        $orm->path = $domain->getPath()->value();
        $orm->slug = $domain->getSlug()->value();
        $orm->sortOrder = $domain->getSortOrder()->value();
        $orm->status = $domain->getStatus()->value();

        if (null !== $domain->getParentId()) {
            $orm->parent = $this->entityManager->getReference(OrmCategory::class, $domain->getParentId()->value());
        } else {
            $orm->parent = null;
        }

        // Map translations
        $currentTranslations = [];
        foreach ($orm->translations as $translation) {
            $currentTranslations[$translation->locale] = $translation;
        }

        foreach ($domain->getTranslations() as $locale => $data) {
            if (isset($currentTranslations[$locale])) {
                $currentTranslations[$locale]->name = $data['name'];
                $currentTranslations[$locale]->description = $data['description'] ?? null;
                unset($currentTranslations[$locale]);
            } else {
                $translation = new OrmCategoryTranslation();
                $translation->category = $orm;
                $translation->locale = $locale;
                $translation->name = $data['name'];
                $translation->description = $data['description'] ?? null;
                $orm->translations->add($translation);
            }
        }

        foreach ($currentTranslations as $translation) {
            $orm->translations->removeElement($translation);
        }
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    private function mapTranslations(object $domain, object $orm): void
    {
        $this->assertIsType(Category::class, $domain);
        $this->assertIsType(OrmCategory::class, $orm);
        /** @var Category $domain */
        /** @var OrmCategory $orm */

        $domainTrans = $domain->getTranslations();

        foreach ($orm->translations as $ormTrans) {
            if (!isset($domainTrans[$ormTrans->locale])) {
                $orm->translations->removeElement($ormTrans);
            }
        }

        foreach ($domainTrans as $locale => $vo) {
            $existing = $orm->translations->filter(fn (OrmCategoryTranslation $t) => $t->locale === $locale)->first();

            if ($existing) {
                $existing->name = $vo->name;
                $existing->description = $vo->description;
            } else {
                $newTrans = new OrmCategoryTranslation();
                $newTrans->category = $orm;
                $newTrans->locale = $locale;
                $newTrans->name = $vo->name;
                $newTrans->description = $vo->description;

                $orm->translations->add($newTrans);
            }
        }
    }
}
