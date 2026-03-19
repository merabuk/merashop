<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Shared\Domain\Entity\HasIdInterface;
use App\Shared\Domain\Exception\Database\OneOfEntitiesNotFoundException;
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
    protected function _existsBy(array $criteria): bool
    {
        $qb = $this->createQueryBuilder('e')->select('1');

        foreach ($criteria as $index => $criteriaItem) {
            $field = $criteriaItem->field;
            $operator = $criteriaItem->operator->value;
            $paramName = $field.$index;

            $qb->andWhere("e.{$field} {$operator} :{$paramName}")
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
    ): PaginatedResult {
        $sortField = $sort->field ?? 'id';
        $direction = $sort->direction ?? Sort::ASC;

        if ($cursor->lastSeenIdentifier) {
            $operator = (Sort::DESC === $direction) ? '<' : '>';
            $qb->andWhere("{$alias}.{$sortField} {$operator} :lastId")
                ->setParameter('lastId', $cursor->lastSeenIdentifier);
        }

        $qb->orderBy("{$alias}.{$sortField}", $direction);
        if ('id' !== $sortField) {
            $qb->addOrderBy("{$alias}.id", $direction);
        }

        $qb->setMaxResults($cursor->perPage);

        $paginator = new Paginator($qb, fetchJoinCollection: true);

        $totalCount = $paginator->count();
        $results = [];
        foreach ($paginator as $ormEntity) {
            $results[] = $ormEntity;
        }

        $items = array_map($mapCallback, $results);

        $lastItem = end($items);
        $nextCursor = null;

        // TODO: solution for admin api, refactor this when pagination will be needed for public api
        if ($lastItem instanceof HasIdInterface && null !== $lastItem->getId()) {
            $nextCursor = (string) $lastItem->getId()->value();
        }

        return new PaginatedResult(
            items: $items,
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
