<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service;

use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryMoveToChildConflictException;
use App\Catalog\Domain\Service\CategoryValidator;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\TestCase;

final class CategoryValidatorTest extends TestCase
{
    private CategoryValidator $categoryValidator;

    public function setUp(): void
    {
        $this->categoryValidator = new CategoryValidator();
    }

    public function testCanBeAttachedParent(): void
    {
        $category = CategoryMother::createWithData(path: '/category', id: 123);
        $anotherCategory = CategoryMother::createWithData(path: '/category-another', id: 456);

        $this->categoryValidator->canBeAttachedParent(category: $category, newParent: null);
        $this->categoryValidator->canBeAttachedParent(category: $category, newParent: $anotherCategory);

        $this->expectNotToPerformAssertions();
    }

    public function testThrowsExceptionWhenCategoryIsParentOfItself(): void
    {
        $category = CategoryMother::createWithData(id: 123);

        $this->expectException(CategoryCannotBeParentOfItselfException::class);

        $this->categoryValidator->canBeAttachedParent(category: $category, newParent: $category);
    }

    public function testThrowsExceptionWhenCategoryMoveToChild(): void
    {
        $category = CategoryMother::createWithData(path: '/category/owns/descendants', id: 123);
        $childCategory = CategoryMother::createWithData(
            parentId: $category->getId()->value(),
            path: '/category/owns/descendants/child',
            id: 456
        );

        $this->expectException(CategoryMoveToChildConflictException::class);

        $this->categoryValidator->canBeAttachedParent(category: $category, newParent: $childCategory);
    }

    public function testItAllowsMovingToCategoryWithSimilarPrefix(): void
    {
        $category = CategoryMother::createWithData(path: '/home', id: 123);
        $newParent = CategoryMother::createWithData(path: '/home-decor', id: 456);

        $this->categoryValidator->canBeAttachedParent(category: $category, newParent: $newParent);

        $this->expectNotToPerformAssertions();
    }
}
