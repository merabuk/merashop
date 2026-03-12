<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service\Category;

use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Service\Category\CategoryStructureService;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryStructureServiceTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $readRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
    }

    public function testItPreparesNewStructureWithParentId(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(
            path: '/old-path',
            slug: 'old-slug',
            sortOrder: 10,
            id: 123
        );
        $parent = CategoryMother::createWithData(path: '/parent-path', slug: 'parent-slug', id: 456);

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::equalTo($parent->getId()))
            ->willReturn($parent);

        $this->readRepository->expects(self::once())
            ->method('getMaxSortOrder')
            ->with(self::equalTo($parent->getId()))
            ->willReturn(5);

        $result = $this->createService()->prepareNewStructure(
            category: $category,
            newSlug: $newSlug,
            newParentId: $parent->getId()
        );

        self::assertSame('/parent-path/new-slug', $result->newPath->value());
        self::assertSame(6, $result->newSortOrder->value());
        self::assertTrue($parent->getId()->equals($result->newParentId));
    }

    public function testItPreparesNewStructureWithNullParentId(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(
            parentId: 456,
            path: '/old-path',
            slug: 'old-slug',
            sortOrder: 10,
            id: 123
        );

        $this->readRepository->expects(self::never())->method('getById');

        $this->readRepository->expects(self::once())
            ->method('getMaxSortOrder')
            ->with(self::equalTo(null))
            ->willReturn(5);

        $result = $this->createService()->prepareNewStructure(
            category: $category,
            newSlug: $newSlug,
            newParentId: null
        );

        self::assertSame('/new-slug', $result->newPath->value());
        self::assertSame(6, $result->newSortOrder->value());
        self::assertNull($result->newParentId);
    }

    public function testThrowsExceptionWhenParentCategoryDoesNotExist(): void
    {
        $newSlug = Slug::fromString('new-slug');
        $category = CategoryMother::createWithData(parentId: 456);
        $fakeParentId = $category->getParentId();

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::equalTo($fakeParentId))
            ->willThrowException(new CategoryNotFoundException());

        $this->readRepository->expects(self::never())->method('getMaxSortOrder');

        $this->expectException(CategoryParentNotFoundException::class);

        $this->createService()->prepareNewStructure(
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

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::equalTo($fakeParentId))
            ->willReturn($category);

        $this->readRepository->expects(self::never())->method('getMaxSortOrder');

        $this->expectException(CategoryCannotBeParentOfItselfException::class);

        $this->createService()->prepareNewStructure(
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

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::equalTo($childCategory->getId()))
            ->willReturn($childCategory);

        $this->readRepository->expects(self::never())->method('getMaxSortOrder');

        $this->expectException(CategoryChildCanNotBeParentConflictException::class);

        $this->createService()->prepareNewStructure(
            category: $category,
            newSlug: $newSlug,
            newParentId: $childCategory->getId()
        );
    }

    private function createService(): CategoryStructureService
    {
        return new CategoryStructureService(readRepository: $this->readRepository);
    }
}
