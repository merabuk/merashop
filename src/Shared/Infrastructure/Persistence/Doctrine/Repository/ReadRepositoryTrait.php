<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Exception\Database\OneOfEntitiesNotFoundException;
use App\Shared\Domain\ValueObject\IdInterface;
use Doctrine\DBAL\LockMode;
use InvalidArgumentException;

trait ReadRepositoryTrait
{
    /**
     * @param array<int, array{field?: string, value?: mixed, type?: mixed}> $criteria
     */
    protected function _existsBy(array $criteria): bool
    {
        $qb = $this->createQueryBuilder('e')->select('1');

        foreach ($criteria as $index => $criteriaItem) {
            $field = $criteriaItem['field'] ?? throw new InvalidArgumentException('Criteria item must have a field');
            $value = $criteriaItem['value'] ?? throw new InvalidArgumentException('Criteria item must have a value');
            $type = $criteriaItem['type'] ?? null;
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
}
