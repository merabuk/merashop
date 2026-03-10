<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageIdException;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageUlidException;
use App\Catalog\Domain\ValueObject\TemporaryImage\Context;
use App\Catalog\Domain\ValueObject\TemporaryImage\Id;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmTemporaryImage;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<TemporaryImage, OrmTemporaryImage>
 */
class TemporaryImageMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmTemporaryImage
    {
        $this->assertIsType(TemporaryImage::class, $domain);
        /** @var TemporaryImage $domain */
        $orm = new OrmTemporaryImage();

        $orm->ulid = $domain->getUlid()->value();
        $orm->path = $domain->getPath()->value();
        $orm->context = $domain->getContext()->value();

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidRelativePathException
     * @throws InvalidTemporaryImageIdException
     * @throws InvalidTemporaryImageUlidException
     */
    public function fromDoctrineOrm(object $orm): TemporaryImage
    {
        $this->assertIsType(OrmTemporaryImage::class, $orm);
        /** @var OrmTemporaryImage $orm */
        $id = Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class));

        return new TemporaryImage(
            ulid: Ulid::fromString($orm->ulid),
            path: RelativeFilePath::fromString($orm->path),
            context: Context::fromEnum($orm->context),
            id: $id,
        );
    }

    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(TemporaryImage::class, $domain);
        $this->assertIsType(OrmTemporaryImage::class, $orm);
        /* @var TemporaryImage $domain */
        /* @var OrmTemporaryImage $orm */
        // no editable fields
    }
}
