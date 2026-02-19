<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Criteria\Listing\PaginatedResult;
use App\Shared\Domain\Criteria\Paging\Cursor;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Shared\Domain\Entity\HasIdInterface;
use App\Shared\Domain\Exception\Database\OneOfEntitiesNotFoundException;
use App\Shared\Domain\ValueObject\IdInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Criteria\Restrictions\Criterion;
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
            $value = $criteriaItem->value;
            $type = $criteriaItem->type;
            $paramName = $field.$index;

            $qb->andWhere("e.{$field} = :{$paramName}")
                ->setParameter(key: $paramName, value: $value, type: $type);
        }

        $result = $qb->setMaxResults(1)->getQuery()->getScalarResult();

        return count($result) > 0;
    }

    /**
     * @param IdInterface[] $ids
     *
     * @throws OneOfEntitiesNotFoundException
     */
    protected function _assertAllExistByIds(array $ids): void
    {
        $count = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.id IN (:ids)')
            ->setParameter('ids', array_map(fn (IdInterface $id) => $id->value(), $ids))
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $count !== count($ids)) {
            throw new OneOfEntitiesNotFoundException('One or more entities not found');
        }
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

    protected function _makeCriterion(string $field, mixed $value, mixed $type = null): Criterion
    {
        return new Criterion(field: $field, value: $value, type: $type);
    }

    protected function _prepareSearchValue(string $value): string
    {
        return sprintf('%%%s%%', mb_trim(addcslashes($value, '%_')));
    }
}
