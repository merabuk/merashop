<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
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

final class AttributeReadRepository extends BaseAttributeRepository implements AttributeReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws AttributeNotFoundException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function getById(Id $id, bool $withTranslations = true): Attribute
    {
        return $this->findById($id) ?? throw new AttributeNotFoundException();
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findById(Id $id, bool $withTranslations = true): ?Attribute
    {
        $qb = $this->createQueryBuilder('a');

        if ($withTranslations) {
            $qb->leftJoin('a.translations', 't')
                ->addSelect('t');
        }

        $orm = $qb->where('a.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findByUlid(Ulid $ulid): ?Attribute
    {
        $orm = $this->findOneBy(['ulid' => $ulid->value()]);

        return $this->checkAndMapToDomain($orm);
    }

    public function existsByCode(Code $code): bool
    {
        return $this->_existsBy([
            $this->_makeCriterion(field: 'code', value: $code->value()),
        ]);
    }

    /**
     * @param Id[] $ids
     *
     * @throws OneOfAttributesNotFoundException
     */
    public function assertAllExistByIds(array $ids): void
    {
        try {
            $this->_assertAllExistByIds(ids: $ids, alias: 'a');
        } catch (OneOfEntitiesNotFoundException $e) {
            throw new OneOfAttributesNotFoundException(previous: $e);
        }
    }

    /**
     * @return PaginatedResult<Attribute>
     */
    public function paginate(Criteria $criteria): PaginatedResult
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.translations', 't')
            ->addSelect('t');

        if ($criteria->filters->has('search')) {
            $search = $this->_prepareSearchValue($criteria->filters->get('search'));

            $qb->andWhere($qb->expr()->orX(
                'a.code LIKE :search',
                't.name LIKE :search'
            ))->setParameter('search', $search);
        }

        return $this->_paginate(
            qb: $qb,
            cursor: $criteria->cursor,
            sort: $criteria->sort,
            mapCallback: fn (object $orm) => $this->checkAndMapToDomain($orm),
            alias: 'a'
        );
    }

    /**
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
