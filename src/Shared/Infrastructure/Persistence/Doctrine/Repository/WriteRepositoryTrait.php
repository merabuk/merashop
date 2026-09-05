<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use RuntimeException;

/**
 * @template TDomain of object
 * @template TOrm of object
 */
trait WriteRepositoryTrait
{
    /**
     * @param TDomain $domain
     *
     * @return TOrm
     *
     * @throws IncompatibleMappedEntityException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function _save(object $domain, int|string|null $id): object
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

            /** @var TOrm $orm */
            $mapper->mapToExistingOrm($domain, $orm);
        } else {
            $orm = $mapper->toDoctrineOrm($domain);
            $em->persist($orm);
        }

        $em->flush();

        return $orm;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function _delete(int|string|null $id): void
    {
        if (null === $id) {
            throw new RuntimeException(sprintf('Entity %s must have an ID to delete', self::getEntityClass()));
        }
        $stringId = (string) $id;

        $em = $this->getEntityManager();

        $orm = $em->getUnitOfWork()->tryGetById($stringId, self::getEntityClass()) ?: null;
        $orm ??= $this->findOrmForDeleteFallback((string) $id);

        if ($orm) {
            $em->remove($orm);
            $em->flush();
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function findOrmForUpdateFallback(string $stringId): ?object
    {
        return $this->findOrmById($stringId);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function findOrmForDeleteFallback(string $stringId): ?object
    {
        return $this->findOrmById($stringId);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function findOrmById(string $stringId): ?object
    {
        return $this->getEntityManager()->find(self::getEntityClass(), $stringId);
    }

    private function makeRuntimeException(string $stringId): RuntimeException
    {
        return new RuntimeException(sprintf('Entity %s with ID %s not found', self::getEntityClass(), $stringId));
    }

    /**
     * @return MapperInterface<TDomain, TOrm>
     */
    private function requireMapper(string $method): MapperInterface
    {
        $mapper = $this->mapper;

        if (false === $mapper instanceof MapperInterface) {
            throw $this->makeMapperException($mapper, $method);
        }

        return $mapper;
    }

    private function makeMapperException(object $mapper, string $method): RuntimeException
    {
        return new RuntimeException(sprintf(
            'Mapper %s instance must implement %s to use this method %s',
            get_class($mapper),
            MapperInterface::class,
            $method
        ));
    }
}
