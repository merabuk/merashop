<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\Attribute\AttributeStateException;
use App\Catalog\Domain\Exception\Attribute\OneOfAttributesNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Shared\Domain\Criteria\Listing\Criteria;
use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Domain\Exception\Database\OneOfEntitiesNotFoundException;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;
use Doctrine\ORM\QueryBuilder;

final class AttributeReadRepository extends BaseAttributeRepository implements AttributeReadRepositoryInterface
{
    use ReadRepositoryTrait;

    private const string ALIAS = 'a';
    private const string ALIAS_TRANSLATIONS = 't';
    private const string ALIAS_OPTIONS = 'o';
    private const string ALIAS_OPTION_TRANSLATIONS = 'ot';

    /**
     * @throws AttributeNotFoundException
     * @throws AttributeStateException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function getById(Id $id): Attribute
    {
        return $this->findById($id) ?? throw AttributeNotFoundException::withId($id->value());
    }

    /**
     * @throws AttributeStateException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findById(Id $id): ?Attribute
    {
        $qb = $this->createBaseQueryBuilder();
        $this->joinRelations($qb);

        $orm = $this->_findById(id: $id, alias: self::ALIAS, qb: $qb);

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @param Id[] $ids
     *
     * @return Attribute[]
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->createBaseQueryBuilder();
        $this->joinRelations($qb);

        return $this->_findByIds(
            ids: $ids,
            mapCallback: fn (object $orm) => $this->checkAndMapToDomain($orm),
            alias: self::ALIAS,
            qb: $qb
        );
    }

    /**
     * @throws AttributeStateException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findByUlid(Ulid $ulid): ?Attribute
    {
        $qb = $this->createBaseQueryBuilder();

        $this->joinRelations($qb);

        $orm = $this->_findByUlid(ulid: $ulid, alias: self::ALIAS, qb: $qb);

        return $this->checkAndMapToDomain($orm);
    }

    public function existsByCode(Code $code): bool
    {
        return $this->_existsBy(criteria: [
            $this->_makeCriterion(field: 'code', value: $code->value()),
        ], alias: self::ALIAS);
    }

    /**
     * @param Id[] $ids
     *
     * @throws OneOfAttributesNotFoundException
     */
    public function assertAllExistByIds(array $ids): void
    {
        try {
            $this->_assertAllExistByIds(ids: $ids, alias: self::ALIAS);
        } catch (OneOfEntitiesNotFoundException $e) {
            throw new OneOfAttributesNotFoundException(previous: $e);
        }
    }

    /**
     * @return PaginatedResult<Attribute>
     */
    public function paginate(Criteria $criteria): PaginatedResult
    {
        $qb = $this->createBaseQueryBuilder();

        $this->joinRelations(qb: $qb, withOptions: false);

        if ($criteria->filters->has('search')) {
            $search = $this->_prepareSearchValue($criteria->filters->get('search'));

            $qb->andWhere($qb->expr()->orX(
                self::ALIAS.'.code LIKE :search',
                self::ALIAS_TRANSLATIONS.'.name LIKE :search'
            ))->setParameter('search', $search);
        }

        return $this->_paginate(
            qb: $qb,
            cursor: $criteria->cursor,
            sort: $criteria->sort,
            mapCallback: fn (object $orm) => $this->checkAndMapToDomain($orm),
            alias: self::ALIAS,
        );
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder(self::ALIAS);
    }

    private function joinRelations(QueryBuilder $qb, bool $withOptions = true): void
    {
        $this->joinTranslations($qb);

        if ($withOptions) {
            $this->joinOptions($qb);
        }
    }

    private function joinTranslations(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.translations', self::ALIAS_TRANSLATIONS)
            ->addSelect(self::ALIAS_TRANSLATIONS);
    }

    private function joinOptions(QueryBuilder $qb): void
    {
        $qb->leftJoin(self::ALIAS.'.options', self::ALIAS_OPTIONS)
            ->addSelect(self::ALIAS_OPTIONS)
            ->leftJoin(self::ALIAS_OPTIONS.'.translations', self::ALIAS_OPTION_TRANSLATIONS)
            ->addSelect(self::ALIAS_OPTION_TRANSLATIONS);
    }

    /**
     * @throws AttributeStateException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function checkAndMapToDomain(?object $orm): ?Attribute
    {
        if (false === $orm instanceof OrmAttribute) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($orm);
    }
}
