<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Enum\Category\SortFieldEnum;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Domain\Exception\Database\OneOfEntitiesNotFoundException;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Criteria\Restrictions\ComparisonOperatorEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;
use Doctrine\ORM\QueryBuilder;

final class CategoryReadRepository extends BaseCategoryRepository implements CategoryReadRepositoryInterface
{
    use ReadRepositoryTrait;

    private const string ALIAS = 'c';
    private const string ALIAS_TRANSLATIONS = 't';
    private const string ALIAS_PARENT = 'p';

    /**
     * @throws EntityIdMissingException
     * @throws CategoryNotFoundException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function getById(Id $id, bool $withParent = true, bool $withTranslations = true): Category
    {
        return $this->findById($id, $withParent, $withTranslations) ?? throw new CategoryNotFoundException();
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findById(Id $id, bool $withParent = true, bool $withTranslations = true): ?Category
    {
        $qb = $this->createBaseQueryBuilder();

        if ($withParent) {
            $this->joinParent($qb);
        }

        if ($withTranslations) {
            $this->joinTranslations(qb: $qb);
        }

        $orm = $this->_findById(id: $id, alias: self::ALIAS, qb: $qb);

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findByUlid(Ulid $ulid, bool $withParent = true, bool $withTranslations = true): ?Category
    {
        $qb = $this->createBaseQueryBuilder();

        if ($withParent) {
            $this->joinParent($qb);
        }

        if ($withTranslations) {
            $this->joinTranslations($qb);
        }

        $orm = $this->_findByUlid(ulid: $ulid, alias: self::ALIAS, qb: $qb);

        return $this->checkAndMapToDomain($orm);
    }

    public function getMaxSortOrder(?Id $parentId): int
    {
        $qb = $this->createBaseQueryBuilder();

        $qb->select(sprintf('MAX(%s.sortOrder)', self::ALIAS));

        if (null === $parentId) {
            $qb->where(sprintf('%s.parent IS NULL', self::ALIAS));
        } else {
            $qb->where(sprintf('%s.parent = :parentId', self::ALIAS))
                ->setParameter('parentId', $parentId->value());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Id[] $ids
     *
     * @throws OneOfCategoriesNotFoundException
     */
    public function assertAllExistByIds(array $ids): void
    {
        try {
            $this->_assertAllExistByIds(ids: $ids, alias: self::ALIAS);
        } catch (OneOfEntitiesNotFoundException $e) {
            throw new OneOfCategoriesNotFoundException(previous: $e);
        }
    }

    public function existsBySlug(Slug $slug, ?Id $excludeId = null): bool
    {
        $criteria = [
            $this->_makeCriterion(field: 'slug', value: $slug->value()),
        ];

        if (null !== $excludeId) {
            $criteria[] = $this->_makeCriterion(
                field: 'id',
                value: $excludeId->value(),
                operator: ComparisonOperatorEnum::NotEqual
            );
        }

        return $this->_existsBy(criteria: $criteria, alias: self::ALIAS);
    }

    /**
     * @return PaginatedResult<Category>
     */
    public function paginate(Criteria $criteria): PaginatedResult
    {
        $qb = $this->createBaseQueryBuilder();

        $this->joinRelations(qb: $qb);

        if ($criteria->filters->has('search')) {
            $search = $this->_prepareSearchValue($criteria->filters->get('search'));

            $qb->andWhere($qb->expr()->orX(
                self::ALIAS.'.slug LIKE :search',
                self::ALIAS_TRANSLATIONS.'.name LIKE :search',
            ))->setParameter('search', $search);
        }

        $sort = $criteria->sort?->fromField(match (SortFieldEnum::tryFrom($criteria->sort->field)) {
            SortFieldEnum::Slug => self::ALIAS.'.slug',
            SortFieldEnum::Name => self::ALIAS_TRANSLATIONS.'.name',
            null => throw new InvalidArgumentException("Invalid sort field: {$criteria->sort->field}"),
        });

        return $this->_paginate(
            qb: $qb,
            cursor: $criteria->cursor,
            sort: $sort,
            mapCallback: fn (object $row) => $this->checkAndMapToDomain($row),
            alias: self::ALIAS,
        );
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

    private function joinRelations(QueryBuilder $qb): void
    {
        $this->joinParent($qb);
        $this->joinTranslations(qb: $qb);
    }

    private function joinTranslations(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.translations', self::ALIAS_TRANSLATIONS)
            ->addSelect(self::ALIAS_TRANSLATIONS);
    }

    private function joinParent(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.parent', self::ALIAS_PARENT)
            ->addSelect(self::ALIAS_PARENT);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function checkAndMapToDomain(?object $orm): ?Category
    {
        if (false === $orm instanceof OrmCategory) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($orm);
    }
}
