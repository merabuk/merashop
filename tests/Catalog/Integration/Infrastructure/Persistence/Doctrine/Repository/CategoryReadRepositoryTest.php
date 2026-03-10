<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\OneOfCategoriesNotFoundException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Tests\Catalog\Support\Traits\CatalogEntityManagerTrait;
use App\Tests\Catalog\Support\Traits\CategoryFactoryTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryReadRepositoryTest extends KernelTestCase
{
    use CategoryFactoryTrait;
    use CatalogEntityManagerTrait;
    use ValueObjectAssertionTrait;

    private CategoryReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(CategoryReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $category = $this->getCategoryFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($category->getId());

        self::assertNotNull($found);
        self::assertTrue($category->getUlid()->equals($found->getUlid()));
        $this->assertVoEqualsOrNull($category->getParentId(), $found->getParentId());
        self::assertTrue($category->getPath()->equals($found->getPath()));
        self::assertTrue($category->getSlug()->equals($found->getSlug()));
        self::assertTrue($category->getSortOrder()->equals($found->getSortOrder()));
        self::assertTrue($category->getStatus()->equals($found->getStatus()));
        self::assertCount($category->getTranslations()->count(), $found->getTranslations());
        foreach ($category->getTranslations() as $locale => $translation) {
            $foundTranslation = $found->getTranslations()->get($locale);
            self::assertNotNull($foundTranslation);
            self::assertSame($translation->name, $foundTranslation->name);
            self::assertSame($translation->description, $foundTranslation->description);
        }
        self::assertTrue($category->getVersion()->equals($found->getVersion()));
        self::assertTrue($category->getCreatedBy()->equals($found->getCreatedBy()));
        $this->assertVoEqualsOrNull($category->getUpdatedBy(), $found->getUpdatedBy());
    }

    public function testGetByIdThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(CategoryNotFoundException::class);

        $this->repository->getById(Id::fromInt(1));
    }

    public function testFindByUlid(): void
    {
        $category = $this->getCategoryFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($category->getUlid());

        self::assertNotNull($found);
        self::assertTrue($category->getId()->equals($found->getId()));
    }

    public function testGetMaxSortOrder(): void
    {
        $parent = $this->getCategoryFixture()->create();
        $child = $this->getCategoryFixture()->create(parentId: $parent->getId()->value());

        $maxSortOrder = $this->repository->getMaxSortOrder($parent->getId());
        self::assertSame($child->getSortOrder()->value(), $maxSortOrder);
    }

    public function testAssertAllExistByIds(): void
    {
        $category1 = $this->getCategoryFixture()->create();
        $category2 = $this->getCategoryFixture()->create();

        $this->repository->assertAllExistByIds([$category1->getId(), $category2->getId()]);
        self::assertTrue(true);
    }

    public function testAssertAllExistByIdsThrowsExceptionOnFailure(): void
    {
        $attr1 = $this->getCategoryFixture()->create();
        $invalidId = Id::fromInt(1);

        $this->expectException(OneOfCategoriesNotFoundException::class);

        $this->repository->assertAllExistByIds([$attr1->getId(), $invalidId]);
    }

    public function testExistsBySlug(): void
    {
        $slug = Slug::fromString('category-slug');

        self::assertFalse($this->repository->existsBySlug($slug));

        $this->getCategoryFixture()->create(slug: $slug->value());

        self::assertTrue($this->repository->existsBySlug($slug));
    }
}
