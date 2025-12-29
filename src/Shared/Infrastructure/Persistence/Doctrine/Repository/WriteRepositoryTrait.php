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
            $stringId = (string) $id;

            $orm = $em->getUnitOfWork()->tryGetById($stringId, self::getEntityClass());

            if (!$orm) {
                $orm = $em->find(self::getEntityClass(), $stringId);
            }

            if (!$orm) {
                throw new \RuntimeException(sprintf('Entity %s with ID %s not found', self::getEntityClass(), $stringId));
            }

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
