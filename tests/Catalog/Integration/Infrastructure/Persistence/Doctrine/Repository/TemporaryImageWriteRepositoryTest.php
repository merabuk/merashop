<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmTemporaryImage;
use App\Tests\Catalog\Support\Traits\CatalogEntityManagerTrait;
use App\Tests\Catalog\Support\Traits\TemporaryImageFactoryTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TemporaryImageWriteRepositoryTest extends KernelTestCase
{
    use TemporaryImageFactoryTrait;
    use CatalogEntityManagerTrait;
    use EntityTechnicalMetadataTrait;

    private TemporaryImageWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(TemporaryImageWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $temporaryImage = $this->getTemporaryImageMother()->create();

        $created = $this->repository->save($temporaryImage);

        self::assertNotNull($created->getId());
        self::assertTrue($temporaryImage->getUlid()->equals($created->getUlid()));
        self::assertTrue($temporaryImage->getPath()->equals($created->getPath()));
        self::assertTrue($temporaryImage->getContext()->equals($created->getContext()));
    }

    public function testDelete(): void
    {
        $temporaryImage = $this->getTemporaryImageFixture()->create();
        $this->clearEntityManager();

        $this->repository->delete($temporaryImage);

        self::assertNull($this->getReadRepository()->findById($temporaryImage->getId()));
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $temporaryImage = $this->getTemporaryImageFixture()->create();
        $id = $temporaryImage->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmTemporaryImage::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
    }

    private function getReadRepository(): TemporaryImageReadRepositoryInterface
    {
        return self::getContainer()->get(TemporaryImageReadRepositoryInterface::class);
    }
}
