<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ThrowableValueObjectException;
use Doctrine\ORM\Exception\ORMException;

trait WriteRepositoryTrait
{
    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ThrowableValueObjectException
     */
    protected function _save(object $domain, ?int $id): object
    {
        $em = $this->getEntityManager();

        if (null !== $id) {
            $orm = $em->getReference(self::getEntityClass(), $id);

            $this->mapper->mapToExistingOrm($domain, $orm);
        } else {
            $orm = $this->mapper->toDoctrineOrm($domain);
            $em->persist($orm);
        }

        $em->flush();

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    protected function _delete(object $domain): void
    {
        $this->getEntityManager()->remove(
            $this->mapper->toDoctrineOrm($domain)
        );
        $this->getEntityManager()->flush();
    }
}
