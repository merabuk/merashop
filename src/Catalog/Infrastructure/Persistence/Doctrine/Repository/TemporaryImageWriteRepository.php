<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmTemporaryImage;
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\Markers\ValueObjectExceptionInterface;
use App\Shared\Domain\Helpers\TypeCastingTrait;
use App\Shared\Infrastructure\Persistence\Doctrine\Helper\UlidPersistenceHelper;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

final class TemporaryImageWriteRepository extends BaseTemporaryImageRepository implements TemporaryImageWriteRepositoryInterface
{
    use TypeCastingTrait;
    /**
     * @use WriteRepositoryTrait<TemporaryImage, OrmTemporaryImage>
     */
    use WriteRepositoryTrait;

    /**
     * @throws EntityFieldMissingException
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
        if (empty($ulids)) {
            return 0;
        }

        $result = $this->createQueryBuilder('ti')
            ->delete()
            ->where('ti.ulid IN (:ulids)')
            ->setParameter(
                key: 'ulids',
                value: UlidPersistenceHelper::toBaseStrings($ulids),
                type: ArrayParameterType::STRING
            )
            ->getQuery()
            ->execute();

        $this->getEntityManager()->clear();

        return self::castToInt(value: $result);
    }

    public function deleteOlderThan(DateTimeImmutable $date): int
    {
        $result = $this->createQueryBuilder('ti')
            ->delete()
            ->where('ti.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();

        $this->getEntityManager()->clear();

        return self::castToInt(value: $result);
    }
}
