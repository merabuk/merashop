<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategory;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmCategoryTranslation;
use App\Tests\Catalog\Support\Traits\CatalogEntityManagerTrait;
use App\Tests\Catalog\Support\Traits\CategoryFactoryTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryWriteRepositoryTest extends KernelTestCase
{
    use CategoryFactoryTrait;
    use CatalogEntityManagerTrait;
    use EntityTechnicalMetadataTrait;
    use ValueObjectAssertionTrait;

    private CategoryWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(CategoryWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $category = $this->getCategoryMother()->create();

        $created = $this->repository->save($category);

        self::assertNotNull($created->getId());
        self::assertTrue($category->getUlid()->equals($created->getUlid()));
        $this->assertVoEqualsOrNull($category->getParentId(), $created->getParentId());
        self::assertTrue($category->getPath()->equals($created->getPath()));
        self::assertTrue($category->getSlug()->equals($created->getSlug()));
        self::assertTrue($category->getSortOrder()->equals($created->getSortOrder()));
        self::assertTrue($category->getStatus()->equals($created->getStatus()));
        self::assertCount($category->getTranslations()->count(), $created->getTranslations());
        foreach ($category->getTranslations() as $locale => $translation) {
            $createdTranslation = $created->getTranslations()->get($locale);
            self::assertNotNull($createdTranslation);
            self::assertSame($translation->name, $createdTranslation->name);
            self::assertSame($translation->description, $createdTranslation->description);
        }
        self::assertSame(1, $created->getVersion()->value(), 'Initial version should be 1');
        self::assertTrue($category->getCreatedBy()->equals($created->getCreatedBy()));
        self::assertNull($created->getUpdatedBy());
    }

    public function testDelete(): void
    {
        $category = $this->getCategoryFixture()->create();
        $id = $category->getId()->value();

        $ormCategory = $this->findOrmEntity(OrmCategory::class, $id);
        $translationIds = $ormCategory->translations->map(fn (OrmCategoryTranslation $t) => $t->id)->toArray();

        $this->clearEntityManager();

        $this->repository->delete($category);

        self::assertNull(
            actual: $this->getReadRepository()->findById($category->getId()),
            message: 'The category should be deleted'
        );

        foreach ($translationIds as $translationId) {
            self::assertNull(
                actual: $this->findOrmEntity(OrmCategoryTranslation::class, $translationId),
                message: sprintf('Translation with ID %d should be deleted by cascade', $translationId)
            );
        }
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $category = $this->getCategoryFixture()->create();
        $id = $category->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmCategory::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
        $this->assertHasUpdatedAt($ormEntity);
    }

    private function getReadRepository(): CategoryReadRepositoryInterface
    {
        return self::getContainer()->get(CategoryReadRepositoryInterface::class);
    }
}
