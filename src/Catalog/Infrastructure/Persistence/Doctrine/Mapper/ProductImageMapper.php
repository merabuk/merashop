<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Entity\ProductImage;
use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageIdException;
use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageUlidException;
use App\Catalog\Domain\ValueObject\ProductImage\Id;
use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Catalog\Domain\ValueObject\ProductImage\Ulid;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductImage;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

final readonly class ProductImageMapper
{
    /**
     * @throws EntityIdMissingException
     * @throws InvalidProductImageIdException
     * @throws InvalidProductImageUlidException
     * @throws InvalidRelativePathException
     */
    public function toDomain(OrmProductImage $orm): ProductImage
    {
        $id = $orm->id ?? throw EntityIdMissingException::forEntity($orm::class);

        return new ProductImage(
            ulid: Ulid::fromString($orm->ulid),
            path: RelativeFilePath::fromString($orm->path),
            sortOrder: SortOrder::fromInt($orm->sortOrder),
            isMain: MainImageFlag::fromBool($orm->isMain),
            id: Id::fromInt($id),
        );
    }

    public function mapToExistingOrm(ProductImage $domain, OrmProductImage $orm): void
    {
        $orm->path = $domain->getPath()->value();
        $orm->sortOrder = $domain->getSortOrder()->value();
        $orm->isMain = $domain->isMain()->value();
    }
}
