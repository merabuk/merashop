<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Exception\Attribute\AttributeNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Id;
use App\Catalog\Domain\ValueObject\Attribute\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmAttribute;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;

final class AttributeReadRepository extends BaseAttributeRepository implements AttributeReadRepositoryInterface
{
    /**
     * @throws AttributeNotFoundException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     */
    public function getById(Id $id, bool $withTranslations = true): Attribute
    {
        return $this->findById($id) ?? throw new AttributeNotFoundException();
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     */
    public function findById(Id $id, bool $withTranslations = true): ?Attribute
    {
        $qb = $this->createQueryBuilder('a');

        if ($withTranslations) {
            $qb->leftJoin('a.translations', 't')
                ->addSelect('t');
        }

        $orm = $qb->where('a.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     */
    public function findByUlid(Ulid $ulid): ?Attribute
    {
        $orm = $this->findOneBy(['ulid' => $ulid->value()]);

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     */
    private function checkAndMapToDomain(?object $orm): ?Attribute
    {
        if (false === $orm instanceof OrmAttribute) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($orm);
    }
}
