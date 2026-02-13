<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use RuntimeException;

trait WriteRepositoryTrait
{
    /**
     * @throws IncompatibleMappedEntityException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function _save(object $domain, ?int $id): object
    {
        $mapper = $this->requireMapper(__METHOD__);

        $em = $this->getEntityManager();

        if (null !== $id) {
            $stringId = (string) $id;

            $orm = $em->getUnitOfWork()->tryGetById($stringId, self::getEntityClass()) ?: null;
            $orm ??= $this->findOrmForUpdateFallback($stringId);

            if (!$orm) {
                throw $this->makeRuntimeException($stringId);
            }

            $mapper->mapToExistingOrm($domain, $orm);
        } else {
            $orm = $mapper->toDoctrineOrm($domain);
            $em->persist($orm);
        }

        $em->flush();

        return $orm;
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    protected function _delete(object $domain): void
    {
        $mapper = $this->requireMapper(__METHOD__);

        $this->getEntityManager()->remove(
            $mapper->toDoctrineOrm($domain)
        );
        $this->getEntityManager()->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function findOrmForUpdateFallback(string $stringId): ?object
    {
        return $this->getEntityManager()->find(self::getEntityClass(), $stringId);
    }

    protected function makeRuntimeException(string $stringId): RuntimeException
    {
        return new RuntimeException(sprintf('Entity %s with ID %s not found', self::getEntityClass(), $stringId));
    }

    protected function requireMapper(string $method): MapperInterface
    {
        $mapper = $this->mapper;

        if (false === $mapper instanceof MapperInterface) {
            throw $this->makeMapperException($mapper, $method);
        }

        return $mapper;
    }

    protected function makeMapperException(object $mapper, string $method): RuntimeException
    {
        return new RuntimeException(sprintf(
            'Mapper %s instance must implement %s to use this method %s',
            get_class($mapper),
            MapperInterface::class,
            $method
        ));
    }
}
