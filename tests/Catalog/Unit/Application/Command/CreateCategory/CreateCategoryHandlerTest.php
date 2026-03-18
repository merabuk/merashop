<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\CreateCategory;

use App\Catalog\Application\Command\CreateCategory\CreateCategoryCommand;
use App\Catalog\Application\Command\CreateCategory\CreateCategoryHandler;
use App\Catalog\Domain\DTO\CategoryStructureResult;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\Category\CategoryStructureServiceInterface;
use App\Catalog\Domain\Service\Category\CategoryValidatorInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateCategoryHandlerTest extends TestCase
{
    private UlidGeneratorInterface&MockObject $ulidGenerator;
    private CategoryValidatorInterface&MockObject $categoryValidator;
    private CategoryStructureServiceInterface&MockObject $categoryStructureService;
    private CategoryWriteRepositoryInterface&MockObject $writeRepository;

    protected function setUp(): void
    {
        $this->ulidGenerator = $this->createMock(UlidGeneratorInterface::class);
        $this->categoryValidator = $this->createMock(CategoryValidatorInterface::class);
        $this->categoryStructureService = $this->createMock(CategoryStructureServiceInterface::class);
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

        $this->givenSlugIsAvailable($command->slug);
        $this->expectGenerateUlid($category->getUlid()->value());
        $structure = $this->createStructure(
            slug: $command->slug,
            sortOrder: 6,
            parentPath: $parentCategory->getPath()->value(),
            parentId: $command->parentId,
        );
        $this->expectGenerateStructure($command->slug, $command->parentId, $structure);
        $this->expectSaveCategory($command->slug, $structure, $category);

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

        $this->givenSlugIsAvailable($command->slug);
        $this->expectGenerateUlid($category->getUlid()->value());
        $structure = $this->createStructure(slug: $command->slug, sortOrder: 6);
        $this->expectGenerateStructure($command->slug, $command->parentId, $structure);
        $this->expectSaveCategory($command->slug, $structure, $category);

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

        $this->givenSlugIsTaken($command->slug);
        $this->generateUlidNeverCalled();
        $this->generateStructureNeverCalled();
        $this->saveCategoryNeverCalled();

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
            categoryValidator: $this->categoryValidator,
            categoryStructureService: $this->categoryStructureService,
            writeRepository: $this->writeRepository
        );
    }

    private function givenSlugIsAvailable(string $slug): void
    {
        $this->categoryValidator->expects(self::once())
            ->method('validateCreation')
            ->with(self::callback(fn (Slug $s) => $s->value() === $slug));
    }

    private function givenSlugIsTaken(string $slug): void
    {
        $this->categoryValidator->expects(self::once())
            ->method('validateCreation')
            ->with(self::callback(fn (Slug $s) => $s->value() === $slug))
            ->willThrowException(new CategoryAlreadyExistsException());
    }

    private function expectGenerateUlid(string $expectedUlid): void
    {
        $this->ulidGenerator->expects(self::once())
            ->method('next')
            ->willReturn($expectedUlid);
    }

    private function generateUlidNeverCalled(): void
    {
        $this->ulidGenerator->expects(self::never())->method('next');
    }

    private function expectGenerateStructure(string $slug, ?int $parentId, CategoryStructureResult $structure): void
    {
        $this->categoryStructureService->expects(self::once())
            ->method('prepareStructure')
            ->with(
                self::callback(fn (Slug $s) => $s->value() === $slug),
                self::callback(fn (?Id $id) => $id?->value() === $parentId)
            )
            ->willReturn($structure);
    }

    private function generateStructureNeverCalled(): void
    {
        $this->categoryStructureService->expects(self::never())->method('prepareStructure');
    }

    private function expectSaveCategory(
        string $slug,
        CategoryStructureResult $structure,
        Category $category,
    ): void {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Category $savedCategory) use ($slug, $structure): bool {
                $slugCorrect = $slug === $savedCategory->getSlug()->value();
                $pathCorrect = $structure->path->equals($savedCategory->getPath());
                $sortOrderCorrect = $structure->sortOrder->equals($savedCategory->getSortOrder());
                $parentIdCorrect = $structure->parentId?->equals($savedCategory->getParentId()) ?? true;

                return $slugCorrect && $pathCorrect && $sortOrderCorrect && $parentIdCorrect;
            }))
            ->willReturn($category);
    }

    private function saveCategoryNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }

    private function createStructure(
        string $slug,
        int $sortOrder = 5,
        ?string $parentPath = null,
        ?int $parentId = null,
    ): CategoryStructureResult {
        return new CategoryStructureResult(
            path: Path::fromString($parentPath.Path::SEPARATOR.$slug),
            sortOrder: SortOrder::fromInt($sortOrder),
            parentId: $parentId ? Id::fromInt($parentId) : null
        );
    }
}
