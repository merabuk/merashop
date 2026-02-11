<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class CategoryReadRepository extends BaseCategoryRepository implements CategoryReadRepositoryInterface
{
    /**
     * @throws EntityIdMissingException
     * @throws CategoryNotFoundException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function getById(Id $id, bool $withParent = true, bool $withTranslations = true): Category
    {
        return $this->findById($id, $withParent, $withTranslations) ?? throw new CategoryNotFoundException();
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findById(Id $id, bool $withParent = true, bool $withTranslations = true): ?Category
    {
        $qb = $this->createQueryBuilder('c');

        if ($withParent) {
            $qb->leftJoin('c.parent', 'p')
                ->addSelect('p');
        }

        if ($withTranslations) {
            $qb->leftJoin('c.translations', 't')
                ->addSelect('t');
        }

        $orm = $qb->where('c.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    public function findByUlid(Ulid $ulid, bool $withParent = true, bool $withTranslations = true): ?Category
    {
        $qb = $this->createQueryBuilder('c');

        if ($withParent) {
            $qb->leftJoin('c.parent', 'p')
                ->addSelect('p');
        }

        if ($withTranslations) {
            $qb->leftJoin('c.translations', 't')
                ->addSelect('t');
        }

        $orm = $qb->where('c.ulid = :ulid')
            ->setParameter('ulid', $ulid->value(), UlidType::NAME)
            ->getQuery()
            ->getOneOrNullResult();

        return $this->checkAndMapToDomain($orm);
    }

    public function getMaxSortOrder(?Id $parentId): int
    {
        $qb = $this->createQueryBuilder('cr')
            ->select('MAX(cr.sortOrder)');

        if (null === $parentId) {
            $qb->where('cr.parent IS NULL');
        } else {
            $qb->where('cr.parent = :parentId')
                ->setParameter('parentId', $parentId->value());
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidCatalogValueObjectException
     * @throws InvalidLocaleException
     */
    private function checkAndMapToDomain(?object $orm): ?Category
    {
        if (false === $orm instanceof OrmCategory) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($orm);
    }
}
