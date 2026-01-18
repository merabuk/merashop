<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

trait ReadRepositoryTrait
{
    /**
     * @param array<string, string> $criteria
     */
    protected function _existsBy(array $criteria): bool
    {
        $qb = $this->createQueryBuilder('e')->select('1');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("e.{$field} = :{$field}")
                ->setParameter($field, $value);
        }

        return null !== $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }
}
