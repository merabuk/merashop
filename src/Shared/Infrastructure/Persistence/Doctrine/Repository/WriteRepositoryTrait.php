<?php

namespace App\Shared\Infrastructure\Persistence\Doctrine\Repository;

use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use RuntimeException;

trait WriteRepositoryTrait
{
    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    protected function _save(object $domain, ?int $id): object
    {
        $this->checkMapper(__METHOD__);

        $em = $this->getEntityManager();

        if (null !== $id) {
            $stringId = (string) $id;

            $orm = $em->getUnitOfWork()->tryGetById($stringId, self::getEntityClass()) ?: null;
            $orm ??= $this->findOrmForUpdateFallback($stringId);

            if (!$orm) {
                throw $this->makeRuntimeException($stringId);
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
        $this->checkMapper(__METHOD__);

        $this->getEntityManager()->remove(
            $this->mapper->toDoctrineOrm($domain)
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

    protected function checkMapper(string $method): void
    {
        if (false === $this->mapper instanceof MapperInterface) { // @phpstan-ignore-line
            throw new RuntimeException(sprintf('Mapper instance must implement %s to use this method %s or implement custom method manually in repository', MapperInterface::class, $method));
        }
    }
}
