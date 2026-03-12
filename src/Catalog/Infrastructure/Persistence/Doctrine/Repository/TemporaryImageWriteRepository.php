<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use DateTimeImmutable;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class TemporaryImageWriteRepository extends BaseTemporaryImageRepository implements TemporaryImageWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(TemporaryImage $temporaryImage): TemporaryImage
    {
        $orm = $this->_save(domain: $temporaryImage, id: $temporaryImage->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(TemporaryImage $temporaryImage): void
    {
        $this->_delete($temporaryImage->getId()?->value());
    }

    /**
     * @param Ulid[] $ulids
     */
    public function deleteByUlids(array $ulids): int
    {
        return (int) $this->createQueryBuilder('ti')
            ->delete()
            ->where('ti.ulid IN (:ulids)')
            ->setParameter('ulids', $ulids, UlidType::NAME)
            ->getQuery()
            ->execute();
    }

    public function deleteOlderThan(DateTimeImmutable $date): int
    {
        return (int) $this->createQueryBuilder('ti')
            ->delete()
            ->where('ti.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
