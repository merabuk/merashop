<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateCategory;

use App\Catalog\Application\Command\CreateCategory\CreateCategoryCommand;
use App\Catalog\Application\Command\CreateCategory\CreateCategoryHandler;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\TestCase;

final class CreateCategoryHandlerTest extends TestCase
{
    private UlidGeneratorInterface $ulidGenerator;
    private CategoryReadRepositoryInterface $readRepository;
    private CategoryWriteRepositoryInterface $writeRepository;

    protected function setUp(): void
    {
        $this->ulidGenerator = $this->createMock(UlidGeneratorInterface::class);
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(CategoryWriteRepositoryInterface::class);
    }

    public function testItHandleSuccessWithParent(): void
    {
        $fakeId = 123;
        $category = CategoryMother::createWithData(id: $fakeId);
        $fakeParentId = 456;
        $parentCategory = CategoryMother::createWithData(
            path: '/parent-category',
            slug: 'parent-category',
            id: $fakeParentId
        );

        $command = $this->fillAndGetCommand(
            slug: 'child-category',
            parentId: $fakeParentId,
            category: $category
        );

        $this->readRepository->expects(self::once())
            ->method('existsBySlug')
            ->with(self::callback(fn (Slug $slug) => $slug->value() === $command->slug))
            ->willReturn(false);

        $this->ulidGenerator->expects(self::once())->method('next')->willReturn($category->getUlid()->value());

        $this->readRepository->expects(self::once())
            ->method('findById')
            ->with(self::callback(fn (Id $id) => $id->value() === $command->parentId))
            ->willReturn($parentCategory);

        $this->readRepository->expects(self::once())
            ->method('getMaxSortOrder')
            ->with(self::callback(fn (Id $id) => $id->value() === $command->parentId))
            ->willReturn(5);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Category $savedCategory): bool {
                $slugCorrect = 'child-category' === $savedCategory->getSlug()->value();
                $pathCorrect = '/parent-category/child-category' === $savedCategory->getPath()->value();
                $sortOrderCorrect = 6 === $savedCategory->getSortOrder()->value();

                return $slugCorrect && $pathCorrect && $sortOrderCorrect;
            }))
            ->willReturn($category);

        $result = $this->createHandler()($command);

        self::assertSame($fakeId, $result);
    }

    public function testItHandleSuccessWithoutParent(): void
    {
        $fakeId = 123;
        $category = CategoryMother::createWithData(id: $fakeId);

        $command = $this->fillAndGetCommand(
            slug: 'test-category',
            parentId: null,
            category: $category
        );

        $this->readRepository->expects(self::once())
            ->method('existsBySlug')
            ->with(self::callback(fn (Slug $slug) => $slug->value() === $command->slug))
            ->willReturn(false);

        $this->ulidGenerator->expects(self::once())->method('next')->willReturn($category->getUlid()->value());

        $this->readRepository->expects(self::never())->method('findById');

        $this->readRepository->expects(self::once())
            ->method('getMaxSortOrder')
            ->with(self::equalTo(null))
            ->willReturn(5);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Category $savedCategory) {
                $slugCorrect = 'test-category' === $savedCategory->getSlug()->value();
                $pathCorrect = '/test-category' === $savedCategory->getPath()->value();
                $sortOrderCorrect = 6 === $savedCategory->getSortOrder()->value();

                return $slugCorrect && $pathCorrect && $sortOrderCorrect;
            }))
            ->willReturn($category);

        $result = $this->createHandler()($command);

        self::assertSame($fakeId, $result);
    }

    public function testThrowsExceptionIfSlugAlreadyExists(): void
    {
        $fakeId = 123;
        $category = CategoryMother::createWithData(id: $fakeId);

        $command = $this->fillAndGetCommand(
            slug: 'test-category',
            parentId: null,
            category: $category
        );

        $this->readRepository->expects(self::once())
            ->method('existsBySlug')
            ->with(self::callback(fn (Slug $slug) => $slug->value() === $command->slug))
            ->willReturn(true);

        $this->ulidGenerator->expects(self::never())->method('next');
        $this->readRepository->expects(self::never())->method('findById');
        $this->readRepository->expects(self::never())->method('getMaxSortOrder');
        $this->writeRepository->expects(self::never())->method('save');

        $this->expectException(CategoryAlreadyExistsException::class);

        $this->createHandler()($command);
    }

    private function fillAndGetCommand(string $slug, ?int $parentId, Category $category): CreateCategoryCommand
    {
        return new CreateCategoryCommand(
            slug: $slug,
            parentId: $parentId,
            status: $category->getStatus()->value()->value,
            translations: $category->getTranslations()->toArray(),
            adminUlid: $category->getCreatedBy()->value()
        );
    }

    private function createHandler(): CreateCategoryHandler
    {
        return new CreateCategoryHandler(
            ulidGenerator: $this->ulidGenerator,
            readRepository: $this->readRepository,
            writeRepository: $this->writeRepository
        );
    }
}
