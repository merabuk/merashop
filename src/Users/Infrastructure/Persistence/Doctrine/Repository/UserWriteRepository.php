<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Users\Domain\Entity\User;
use App\Users\Domain\Exception\InvalidUserValueObjectException;
use App\Users\Domain\Repository\UserWriteRepositoryInterface;
use Doctrine\ORM\Exception\ORMException;

class UserWriteRepository extends BaseUserRepository implements UserWriteRepositoryInterface
{
    /**
     * @throws ORMException
     * @throws InvalidUserValueObjectException
     */
    public function save(User $user): User
    {
        $ormUser = $this->mapper->toDoctrineOrm($user);

        if (false === is_null($ormUser->getId())) {
            $doctrineReference = $this->getEntityManager()->getReference(
                entityName: self::ORM_ENTITY_CLASS_NAME,
                id: $ormUser->getId()
            );
            if (false === $this->getEntityManager()->contains($doctrineReference)) {
                $this->getEntityManager()->persist($doctrineReference);
            }
        } else {
            $this->getEntityManager()->persist($ormUser);
        }

        $this->getEntityManager()->flush();

        return $this->mapper->fromDoctrineOrm($ormUser);
    }

    public function delete(User $user): void
    {
        $this->getEntityManager()->remove(
            $this->mapper->toDoctrineOrm($user)
        );
        $this->getEntityManager()->flush();
    }
}
