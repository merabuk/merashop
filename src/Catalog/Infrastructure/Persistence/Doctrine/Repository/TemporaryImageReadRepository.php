<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageIdException;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageUlidException;
use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Id;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmTemporaryImage;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;

final class TemporaryImageReadRepository extends BaseTemporaryImageRepository implements TemporaryImageReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidRelativePathException
     * @throws InvalidTemporaryImageIdException
     * @throws InvalidTemporaryImageUlidException
     */
    public function findById(Id $id): ?TemporaryImage
    {
        $orm = $this->find($id->value());

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidRelativePathException
     * @throws InvalidTemporaryImageIdException
     * @throws InvalidTemporaryImageUlidException
     */
    public function findByUlid(Ulid $ulid): ?TemporaryImage
    {
        $orm = $this->findOneBy(['ulid' => $ulid->value()]);

        return $this->checkAndMapToDomain($orm);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidRelativePathException
     * @throws InvalidTemporaryImageIdException
     * @throws InvalidTemporaryImageUlidException
     */
    private function checkAndMapToDomain(?object $orm): ?TemporaryImage
    {
        if (false === $orm instanceof OrmTemporaryImage) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($orm);
    }
}
