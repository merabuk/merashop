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
use Symfony\Component\Clock\MockClock;

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

    public function testDeleteByUlids(): void
    {
        $image1 = $this->getTemporaryImageFixture()->create();
        $image2 = $this->getTemporaryImageFixture()->create();

        $stayingImage = $this->getTemporaryImageFixture()->create();

        $this->clearEntityManager();

        $ulidsToDelete = [$image1->getUlid(), $image2->getUlid()];
        $result = $this->repository->deleteByUlids($ulidsToDelete);

        self::assertSame(2, $result);
        self::assertEmpty($this->getReadRepository()->findByUlids($ulidsToDelete));

        self::assertNotNull($this->getReadRepository()->findByUlid($stayingImage->getUlid()));
    }

    public function testDeleteOlderThan(): void
    {
        $clock = new MockClock();

        $em = $this->getCatalogEntityManager();
        $connection = $em->getConnection();

        $oldImage = $this->getTemporaryImageFixture()->create();
        $newImage = $this->getTemporaryImageFixture()->create();

        $connection->executeStatement(
            'UPDATE temporary_images SET created_at = :date WHERE id = :id',
            [
                'date' => $clock->now()->modify('-2 days')->format('Y-m-d H:i:s'),
                'id' => $oldImage->getId()->value(),
            ]
        );

        $this->clearEntityManager();

        $deletedCount = $this->repository->deleteOlderThan($clock->now()->modify('-1 day'));

        self::assertSame(1, $deletedCount, 'Should delete exactly one old image');

        $readRepository = $this->getReadRepository();
        self::assertNull($readRepository->findById($oldImage->getId()), 'Old image should be gone');
        self::assertNotNull($readRepository->findById($newImage->getId()), 'New image should still exist');
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
