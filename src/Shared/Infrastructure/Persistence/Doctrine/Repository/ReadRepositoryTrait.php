<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Shared\Domain\Entity\HasIdInterface;
use App\Shared\Domain\Entity\HasUlidInterface;
use App\Shared\Domain\Exception\Database\OneOfEntitiesNotFoundException;
use App\Shared\Domain\Exception\InvalidArgumentException;
use App\Shared\Domain\Service\Utility\StringHelper;
use App\Shared\Domain\ValueObject\Contract\IdInterface;
use App\Shared\Domain\ValueObject\Identity\Ulid;
use App\Shared\Infrastructure\Persistence\Doctrine\Criteria\Restrictions\ComparisonOperatorEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Criteria\Restrictions\Criterion;
use App\Shared\Infrastructure\Persistence\Doctrine\Helper\UlidPersistenceHelper;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait ReadRepositoryTrait
{
    /**
     * @param Criterion[] $criteria
     */
    protected function _existsBy(array $criteria, string $alias = 'e'): bool
    {
        $qb = $this->createQueryBuilder($alias)->select('1');

        foreach ($criteria as $index => $criteriaItem) {
            $field = $criteriaItem->field;
            $operator = $criteriaItem->operator->value;
            $paramName = $field.$index;

            $qb->andWhere("{$alias}.{$field} {$operator} :{$paramName}")
                ->setParameter(key: $paramName, value: $criteriaItem->value, type: $criteriaItem->type);
        }

        $result = $qb->setMaxResults(1)->getQuery()->getScalarResult();

        return count($result) > 0;
    }

    /**
     * @param IdInterface[] $ids
     *
     * @throws OneOfEntitiesNotFoundException
     */
    protected function _assertAllExistByIds(array $ids, string $alias = 'e'): void
    {
        $count = $this->createQueryBuilder($alias)
            ->select("COUNT({$alias}.id)")
            ->where("{$alias}.id IN (:ids)")
            ->setParameter('ids', array_map(fn (IdInterface $id) => $id->value(), $ids))
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $count !== count($ids)) {
            throw new OneOfEntitiesNotFoundException('One or more entities not found');
        }
    }

    /**
     * @param Ulid[]      $ulids
     * @param Criterion[] $additionalCriteria
     *
     * @throws OneOfEntitiesNotFoundException
     */
    protected function _assertAllExistByUlids(
        array $ulids,
        array $additionalCriteria = [],
        string $alias = 'e',
    ): void {
        if (empty($ulids)) {
            return;
        }

        $qb = $this->createQueryBuilder($alias)
            ->select("COUNT({$alias}.ulid)")
            ->where("{$alias}.ulid IN (:ulids)")
            ->setParameter(
                key: 'ulids',
                value: UlidPersistenceHelper::toBaseStrings($ulids),
                type: ArrayParameterType::STRING
            );

        foreach ($additionalCriteria as $index => $criterion) {
            $field = $criterion->field;
            $operator = $criterion->operator->value;
            $paramName = $criterion->field.$index;

            $qb->andWhere("{$alias}.{$field} {$operator} :{$paramName}")
                ->setParameter(key: $paramName, value: $criterion->value, type: $criterion->type);
        }

        $count = $qb->getQuery()
            ->getSingleScalarResult();

        if ((int) $count !== count($ulids)) {
            throw new OneOfEntitiesNotFoundException('One or more entities not found');
        }
    }

    protected function _findById(
        IdInterface $id,
        string $alias = 'e',
        ?QueryBuilder $qb = null,
    ): ?object {
        $qb ??= $this->createQueryBuilder($alias);

        return $qb->where("{$alias}.id = :id")
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @template T
     *
     * @param IdInterface[]       $ids
     * @param callable(object): T $mapCallback
     *
     * @return array<T>
     */
    protected function _findByIds(
        array $ids,
        callable $mapCallback,
        string $alias = 'e',
        ?QueryBuilder $qb = null,
    ): array {
        if (empty($ids)) {
            return [];
        }

        $qb ??= $this->createQueryBuilder($alias);

        $result = $qb->where("{$alias}.id IN (:ids)")
            ->setParameter('ids', array_map(fn (IdInterface $id) => $id->value(), $ids))
            ->orderBy("{$alias}.id", Sort::ASC)
            ->getQuery()
            ->getResult();

        return array_map($mapCallback, $result);
    }

    protected function _findByUlid(
        Ulid $ulid,
        string $alias = 'e',
        ?QueryBuilder $qb = null,
    ): ?object {
        $qb ??= $this->createQueryBuilder($alias);

        return $qb->where("{$alias}.ulid = :ulid")
            ->setParameter('ulid', UlidPersistenceHelper::toBaseString($ulid))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @template T
     *
     * @param Ulid[]              $ulids
     * @param callable(object): T $mapCallback
     *
     * @return array<T>
     */
    protected function _findManyByUlids(
        array $ulids,
        callable $mapCallback,
        string $alias = 'e',
    ): array {
        if (empty($ulids)) {
            return [];
        }

        $result = $this->createQueryBuilder($alias)
            ->where("{$alias}.ulid IN (:ulids)")
            ->setParameter(
                key: 'ulids',
                value: UlidPersistenceHelper::toBaseStrings($ulids),
                type: ArrayParameterType::STRING
            )
            ->orderBy("{$alias}.ulid", Sort::ASC)
            ->getQuery()
            ->getResult();

        return array_map($mapCallback, $result);
    }

    protected function _findByIdForUpdate(int $id): ?object
    {
        return $this->createQueryBuilder('e')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }

    protected function _paginate(
        QueryBuilder $qb,
        Cursor $cursor,
        ?Sort $sort,
        callable $mapCallback,
        string $alias = 'e',
        string $identifierField = 'ulid',
    ): PaginatedResult {
        $isUlid = 'ulid' === $identifierField;
        $lastSeenIdentifier = $cursor->lastSeenIdentifier;
        $sortFieldWithAlias = $sort->field ?? $alias.'.'.$identifierField;
        $direction = $sort->direction ?? Sort::ASC;

        if (!str_contains($sortFieldWithAlias, '.')) {
            throw new InvalidArgumentException('Sort field must be in format "alias.field"');
        }

        if ($lastSeenIdentifier) {
            $operator = (Sort::DESC === $direction) ? '<' : '>';
            $qb->andWhere("{$alias}.{$identifierField} {$operator} :identifier")
                ->setParameter(
                    key: 'identifier',
                    value: $isUlid
                        ? UlidPersistenceHelper::toBaseString($lastSeenIdentifier)
                        : $lastSeenIdentifier
                );
        }

        $qb->orderBy("{$sortFieldWithAlias}", $direction);
        if ($identifierField !== StringHelper::after(subject: $sortFieldWithAlias, search: '.')) {
            $qb->addOrderBy("{$alias}.{$identifierField}", $direction);
        }

        $limit = $cursor->perPage;
        $qb->setMaxResults($limit + 1);

        $paginator = new Paginator($qb, fetchJoinCollection: true);

        $totalCount = $paginator->count();
        $ormResults = iterator_to_array($paginator);

        $hasMore = count($ormResults) > $limit;

        if ($hasMore) {
            array_pop($ormResults);
        }

        $domainItems = array_map($mapCallback, $ormResults);

        $nextCursor = null;
        if ($hasMore && !empty($domainItems)) {
            $lastDomainItem = end($domainItems);

            if ($lastDomainItem instanceof HasUlidInterface) {
                $nextCursor = $lastDomainItem->getUlid()->value();
            } elseif ($lastDomainItem instanceof HasIdInterface && null !== $lastDomainItem->getId()) {
                $nextCursor = (string) $lastDomainItem->getId()->value();
            }
        }

        return new PaginatedResult(
            items: $domainItems,
            totalCount: $totalCount,
            nextCursor: $nextCursor
        );
    }

    protected function _makeCriterion(
        string $field,
        mixed $value,
        ComparisonOperatorEnum $operator = ComparisonOperatorEnum::Equal,
        mixed $type = null,
    ): Criterion {
        return new Criterion(field: $field, value: $value, operator: $operator, type: $type);
    }

    protected function _prepareSearchValue(string $value): string
    {
        return sprintf('%%%s%%', mb_trim(addcslashes($value, '%_')));
    }
}
