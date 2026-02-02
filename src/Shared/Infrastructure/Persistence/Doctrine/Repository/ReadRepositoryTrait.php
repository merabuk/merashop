<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\LockMode;

trait ReadRepositoryTrait
{
    /**
     * @param array<int, array{field?: string, value?: mixed, type?: mixed}> $criteria
     */
    protected function _existsBy(array $criteria): bool
    {
        $qb = $this->createQueryBuilder('e')->select('1');

        foreach ($criteria as $index => $criteriaItem) {
            $field = $criteriaItem['field'] ?? throw new \InvalidArgumentException('Criteria item must have a field');
            $value = $criteriaItem['value'] ?? throw new \InvalidArgumentException('Criteria item must have a value');
            $type = $criteriaItem['type'] ?? null;
            $paramName = $field.$index;

            $qb->andWhere("e.{$field} = :{$paramName}")
                ->setParameter(key: $paramName, value: $value, type: $type);
        }

        return null !== $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function _findByIdForUpdate(int $id): ?object
    {
        return $this->createQueryBuilder('e')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }
}
