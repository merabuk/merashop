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
use App\Shared\Domain\Exception\Mappers\EntityFieldMissingException;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

final readonly class ProductImageMapper
{
    /**
     * @throws EntityFieldMissingException
     * @throws InvalidProductImageIdException
     * @throws InvalidProductImageUlidException
     * @throws InvalidRelativePathException
     */
    public function toDomain(OrmProductImage $orm): ProductImage
    {
        $id = $orm->id ?? throw EntityFieldMissingException::forEntityId($orm::class);

        return new ProductImage(
            ulid: Ulid::fromString($orm->ulid ?? throw EntityFieldMissingException::forField(field: 'ulid', className: $orm::class)),
            path: RelativeFilePath::fromString($orm->path ?? throw EntityFieldMissingException::forField(field: 'path', className: $orm::class)),
            sortOrder: SortOrder::fromInt($orm->sortOrder ?? throw EntityFieldMissingException::forField(field: 'sortOrder', className: $orm::class)),
            isMain: MainImageFlag::fromBool($orm->isMain ?? throw EntityFieldMissingException::forField(field: 'isMain', className: $orm::class)),
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
