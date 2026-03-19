<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductImage;
use App\Catalog\Infrastructure\Persistence\Doctrine\Mapper\ProductImageMapper;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Tests\Catalog\Support\ProductImageMother;
use PHPUnit\Framework\TestCase;

final class ProductImageMapperTest extends TestCase
{
    private ProductImageMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ProductImageMapper();
    }

    public function testToDomain(): void
    {
        $orm = new OrmProductImage();
        $orm->setId(123);
        $orm->ulid = ProductImageMother::DEFAULT_ULID;
        $orm->path = 'products/24/12/24/image.jpeg';
        $orm->sortOrder = 0;
        $orm->isMain = true;

        $domain = $this->mapper->toDomain($orm);

        self::assertSame($orm->id, $domain->getId()->value());
        self::assertSame($orm->ulid, $domain->getUlid()->value());
        self::assertSame($orm->path, $domain->getPath()->value());
        self::assertSame($orm->sortOrder, $domain->getSortOrder()->value());
        self::assertSame($orm->isMain, $domain->isMain()->value());
    }

    public function testMapToDomainThrowsExceptionWhenOrmMissingId(): void
    {
        $orm = new OrmProductImage();

        $this->expectException(EntityIdMissingException::class);

        $this->mapper->toDomain($orm);
    }

    public function testMapToExistingOrm(): void
    {
        $domain = ProductImageMother::createWithData(id: 123);
        $orm = new OrmProductImage();

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertNull($orm->id);
        self::assertSame($domain->getPath()->value(), $orm->path);
        self::assertSame($domain->getSortOrder()->value(), $orm->sortOrder);
        self::assertSame($domain->isMain()->value(), $orm->isMain);
    }
}
