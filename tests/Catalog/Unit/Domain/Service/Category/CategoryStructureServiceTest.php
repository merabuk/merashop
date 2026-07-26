<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service\Category;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Service\Category\CategoryStructureService;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Tests\Catalog\Support\CategoryMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;

final class CategoryStructureServiceTest extends BaseUnitTest
{
    private CategoryReadRepositoryInterface&MockObject $readRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
    }

    public function testItPreparesStructureWithParentId(): void
    {
        $slug = Slug::fromString('slug');
        $parent = CategoryMother::createWithData(path: '/parent-path', slug: 'parent-slug', id: 456);

        $expectedSortOrder = 5;
        $this->expectParentCategoryFound($parent);
        $this->expectGetSortOrderCalled($parent, $expectedSortOrder);

        $result = $this->createService()->prepareStructure(slug: $slug, parentId: $parent->getId());

        self::assertSame('/parent-path/slug', $result->path->value());
        self::assertSame($expectedSortOrder + 1, $result->sortOrder->value());
        self::assertTrue($parent->getId()->equals($result->parentId));
    }

    public function testItPreparesStructureWithNullParentId(): void
    {
        $slug = Slug::fromString('slug');

        $expectedOrder = 5;
        $this->getParentCategoryNeverCalled();
        $this->expectGetSortOrderCalled(expectedOrder: $expectedOrder);

        $result = $this->createService()->prepareStructure(slug: $slug, parentId: null);

        self::assertSame('/slug', $result->path->value());
        self::assertSame($expectedOrder + 1, $result->sortOrder->value());
        self::assertNull($result->parentId);
    }

    public function testThrowsExceptionWhenParentCategoryDoesNotExist(): void
    {
        $slug = Slug::fromString('slug');
        $fakeCategoryId = Id::fromInt(123);

        $this->expectParentCategoryNotFound($fakeCategoryId);
        $this->expectGetSortOrderNeverCalled();

        $this->expectException(CategoryParentNotFoundException::class);

        $this->createService()->prepareStructure(slug: $slug, parentId: $fakeCategoryId);
    }

    public function testItPreparesStructureUpdateWithParentId(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(
            path: '/old-path',
            slug: 'old-slug',
            sortOrder: 10,
            id: 123
        );
        $parent = CategoryMother::createWithData(path: '/parent-path', slug: 'parent-slug', id: 456);

        $expectedSortOrder = 5;
        $this->expectParentCategoryFound($parent);
        $this->expectGetSortOrderCalled($parent, $expectedSortOrder);

        $result = $this->createService()->prepareStructureUpdate(
            category: $category,
            newSlug: $newSlug,
            newParentId: $parent->getId()
        );

        self::assertSame('/parent-path/new-slug', $result->path->value());
        self::assertSame($expectedSortOrder + 1, $result->sortOrder->value());
        self::assertTrue($parent->getId()->equals($result->parentId));
    }

    public function testItPreparesStructureUpdateWithNullParentId(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(
            parentId: 456,
            path: '/old-path',
            slug: 'old-slug',
            sortOrder: 10,
            id: 123
        );

        $expectedOrder = 5;
        $this->getParentCategoryNeverCalled();
        $this->expectGetSortOrderCalled(expectedOrder: $expectedOrder);

        $result = $this->createService()->prepareStructureUpdate(
            category: $category,
            newSlug: $newSlug,
            newParentId: null
        );

        self::assertSame('/new-slug', $result->path->value());
        self::assertSame($expectedOrder + 1, $result->sortOrder->value());
        self::assertNull($result->parentId);
    }

    public function testThrowsExceptionWhenParentCategoryDoesNotExistForUpdate(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(parentId: 456);
        $fakeParentId = $category->getParentId();

        $this->expectParentCategoryNotFound($category->getParentId());
        $this->expectGetSortOrderNeverCalled();

        $this->expectException(CategoryParentNotFoundException::class);

        $this->createService()->prepareStructureUpdate(
            category: $category,
            newSlug: $newSlug,
            newParentId: $fakeParentId
        );
    }

    public function testThrowsExceptionWhenWhenCategoryIsParentOfItself(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(id: 123);
        $fakeParentId = $category->getId();

        $this->expectParentCategoryFound($category, $fakeParentId);
        $this->expectGetSortOrderNeverCalled();

        $this->expectException(CategoryCannotBeParentOfItselfException::class);

        $this->createService()->prepareStructureUpdate(
            category: $category,
            newSlug: $newSlug,
            newParentId: $fakeParentId
        );
    }

    public function testThrowsExceptionWhenCategoryMoveToChild(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(
            path: '/category/owns/descendants',
            id: 123
        );
        $childCategory = CategoryMother::createWithData(
            parentId: $category->getId()->value(),
            path: '/category/owns/descendants/child',
            id: 456
        );

        $this->expectParentCategoryFound($childCategory);
        $this->expectGetSortOrderNeverCalled();

        $this->expectException(CategoryChildCanNotBeParentConflictException::class);

        $this->createService()->prepareStructureUpdate(
            category: $category,
            newSlug: $newSlug,
            newParentId: $childCategory->getId()
        );
    }

    private function createService(): CategoryStructureService
    {
        return new CategoryStructureService(readRepository: $this->readRepository);
    }

    private function expectParentCategoryFound(Category $parent, ?Id $parentId = null): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::equalTo($parentId ?? $parent->getId()))
            ->willReturn($parent);
    }

    private function expectParentCategoryNotFound(Id $parentId): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::equalTo($parentId))
            ->willThrowException(new CategoryNotFoundException());
    }

    private function getParentCategoryNeverCalled(): void
    {
        $this->readRepository->expects(self::never())->method('getById');
    }

    private function expectGetSortOrderCalled(?Category $parent = null, int $expectedOrder = 5): void
    {
        $this->readRepository->expects(self::once())
            ->method('getMaxSortOrder')
            ->with(self::equalTo($parent?->getId()))
            ->willReturn($expectedOrder);
    }

    private function expectGetSortOrderNeverCalled(): void
    {
        $this->readRepository->expects(self::never())->method('getMaxSortOrder');
    }
}
