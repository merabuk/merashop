<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service\Category;

use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Service\Category\CategoryValidator;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryValidatorTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $readRepository;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
    }

    public function testItValidatesCreation(): void
    {
        $slug = Slug::fromString('slug');

        $this->givenSlugIsAvailable($slug);

        $this->createValidator()->validateCreation(slug: $slug);
    }

    public function testThrowsExceptionWhenCategorySlugAlreadyExists(): void
    {
        $slug = Slug::fromString('slug');

        $this->givenSlugIsTaken($slug);

        $this->expectException(CategoryAlreadyExistsException::class);

        $this->createValidator()->validateCreation(slug: $slug);
    }

    public function testItValidatesUpdating(): void
    {
        $category = CategoryMother::createWithData(slug: 'old-slug', id: 123);
        $newSlug = Slug::fromString('slug');

        $this->givenSlugIsAvailable($newSlug, $category->getId());

        $this->createValidator()->validateUpdate(
            category: $category,
            version: $category->getVersion()->value(),
            newSlug: $newSlug
        );
    }

    public function testThrowsExceptionWhenCategoryVersionDoesNotMatch(): void
    {
        $category = CategoryMother::createWithData(slug: 'old-slug', id: 123);
        $newSlug = Slug::fromString('new-slug');

        $this->expectException(ConcurrencyException::class);

        $this->checkSlugNeverCalled();

        $this->createValidator()->validateUpdate(
            category: $category,
            version: $category->getVersion()->value() + 1,
            newSlug: $newSlug
        );
    }

    public function testThrowsExceptionWhenCategorySlugAlreadyExistsForUpdating(): void
    {
        $category = CategoryMother::createWithData(slug: 'old-slug', id: 123);
        $newSlug = Slug::fromString('exiting-slug');

        $this->givenSlugIsTaken($newSlug, $category->getId());

        $this->expectException(CategoryAlreadyExistsException::class);

        $this->createValidator()->validateUpdate(
            category: $category,
            version: $category->getVersion()->value(),
            newSlug: $newSlug
        );
    }

    public function testItSkipsSlugCheckWhenSlugNotChangedForUpdating(): void
    {
        $category = CategoryMother::createWithData(slug: 'new-slug', id: 123);
        $newSlug = Slug::fromString('new-slug');

        $this->checkSlugNeverCalled();

        $this->createValidator()->validateUpdate(
            category: $category,
            version: $category->getVersion()->value(),
            newSlug: $newSlug
        );
    }

    private function createValidator(): CategoryValidator
    {
        return new CategoryValidator(readRepository: $this->readRepository);
    }

    private function givenSlugIsAvailable(Slug $slug, ?Id $categoryId = null): void
    {
        $this->expectSlugCheck($slug, false, $categoryId);
    }

    private function givenSlugIsTaken(Slug $slug, ?Id $categoryId = null): void
    {
        $this->expectSlugCheck($slug, true, $categoryId);
    }

    private function expectSlugCheck(Slug $slug, bool $exists, ?Id $categoryId = null): void
    {
        $this->readRepository->expects(self::once())
            ->method('existsBySlug')
            ->with(
                self::equalTo($slug),
                self::equalTo($categoryId)
            )
            ->willReturn($exists);
    }

    private function checkSlugNeverCalled(): void
    {
        $this->readRepository->expects(self::never())->method('existsBySlug');
    }
}
