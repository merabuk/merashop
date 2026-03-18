<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service\Category;

use App\Catalog\Domain\DTO\CategoryStructureResult;
use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Service\Category\CategoryManager;
use App\Catalog\Domain\Service\Category\CategoryStructureServiceInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Tests\Catalog\Support\CategoryMother;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryManagerTest extends TestCase
{
    use ValueObjectAssertionTrait;

    private CategoryStructureServiceInterface&MockObject $structureService;

    public function setUp(): void
    {
        $this->structureService = $this->createMock(CategoryStructureServiceInterface::class);
    }

    public function testUpdateCategoryMoveToRootWithStructuralChange(): void
    {
        $category = CategoryMother::createWithData(
            parentId: 456,
            path: '/old-parent/old-slug',
            slug: 'old-slug',
            sortOrder: 10,
            status: StatusEnum::Inactive,
            translations: ['en' => ['name' => 'Old name', 'description' => 'Old description']],
            id: 123
        );

        $newSlug = Slug::fromString('new-slug');

        $data = $this->getCategoryUpdateData(slug: $newSlug->value(), parentId: null);

        $newStructure = $this->getCategoryStructureResult(
            newSlug: $newSlug,
            parentPath: null,
            parentId: null
        );

        $this->expectGenerateStructure(
            category: $category,
            newSlug: $newSlug,
            newParentId: null,
            newStructure: $newStructure
        );

        $this->createManager()->updateCategory(category: $category, data: $data);

        self::assertTrue($newSlug->equals($category->getSlug()));
        $this->assertVoEqualsOrNull($newStructure->parentId, $category->getParentId());
        self::assertTrue($newStructure->path->equals($category->getPath()));
        self::assertTrue($newStructure->sortOrder->equals($category->getSortOrder()));
        self::assertSame($data->status, $category->getStatus()->value()->value);
        self::assertSame(1, $category->getVersion()->value());
        self::assertCount(count($data->translations), $category->getTranslations());
        foreach ($data->translations as $locale => $translation) {
            $categoryTranslation = $category->getTranslations()->get($locale);
            self::assertNotNull($categoryTranslation);
            self::assertSame($translation['name'], $categoryTranslation->name);
            self::assertSame($translation['description'], $categoryTranslation->description);
        }
        self::assertSame($data->adminUlid, $category->getUpdatedBy()?->value());
    }

    public function testUpdateCategoryMoveToChild(): void
    {
        $parent = CategoryMother::createWithData(
            path: '/parent-slug',
            slug: 'parent-slug',
            id: 456
        );
        $category = CategoryMother::createWithData(
            path: '/old-parent-slug/old-slug',
            slug: 'old-slug',
            sortOrder: 10,
            id: 123
        );

        $newSlug = Slug::fromString('new-slug');

        $data = $this->getCategoryUpdateData(slug: $newSlug->value(), parentId: $parent->getId()->value());

        $newStructure = $this->getCategoryStructureResult(
            newSlug: $newSlug,
            parentPath: $parent->getPath(),
            parentId: $parent->getId()
        );

        $this->expectGenerateStructure(
            category: $category,
            newSlug: $newSlug,
            newParentId: $parent->getId(),
            newStructure: $newStructure
        );

        $this->createManager()->updateCategory(category: $category, data: $data);

        self::assertTrue($newSlug->equals($category->getSlug()));
        $this->assertVoEqualsOrNull($newStructure->parentId, $category->getParentId());
        self::assertTrue($newStructure->path->equals($category->getPath()));
    }

    /**
     * @param class-string $exceptionClass
     */
    #[DataProvider('structureServiceErrorProvider')]
    public function testThrowsExceptionWhenStructureServiceThrowsException(string $exceptionClass): void
    {
        $category = CategoryMother::createWithData(parentId: 456, slug: 'old-slug');

        $data = $this->getCategoryUpdateData(slug: 'slug', parentId: 789);

        $this->structureService->expects(self::once())
            ->method('prepareStructureUpdate')
            ->willThrowException(new $exceptionClass());

        $this->expectException($exceptionClass);

        $this->createManager()->updateCategory($category, $data);
    }

    public static function structureServiceErrorProvider(): iterable
    {
        yield 'try attach to non-existing parent' => [
            'exceptionClass' => CategoryParentNotFoundException::class,
        ];
        yield 'try attach parent to itself' => [
            'exceptionClass' => CategoryCannotBeParentOfItselfException::class,
        ];
        yield 'try make child as parent' => [
            'exceptionClass' => CategoryChildCanNotBeParentConflictException::class,
        ];
    }

    private function getCategoryUpdateData(string $slug, ?int $parentId): CategoryUpdateData
    {
        return new CategoryUpdateData(
            slug: Slug::fromString($slug),
            parentId: $parentId,
            status: StatusEnum::Active->value,
            translations: ['en' => ['name' => 'New name', 'description' => 'New description']],
            adminUlid: CategoryMother::DEFAULT_ADMIN_ULID
        );
    }

    private function getCategoryStructureResult(
        Slug $newSlug,
        ?Path $parentPath,
        ?Id $parentId,
    ): CategoryStructureResult {
        return new CategoryStructureResult(
            path: Path::generate($newSlug, $parentPath),
            sortOrder: SortOrder::fromInt(6),
            parentId: $parentId
        );
    }

    private function createManager(): CategoryManager
    {
        return new CategoryManager(
            structureService: $this->structureService
        );
    }

    private function expectGenerateStructure(
        Category $category,
        Slug $newSlug,
        ?Id $newParentId,
        CategoryStructureResult $newStructure,
    ): void {
        $this->structureService->expects(self::once())
            ->method('prepareStructureUpdate')
            ->with(
                self::equalTo($category),
                self::equalTo($newSlug),
                self::equalTo($newParentId)
            )
            ->willReturn($newStructure);
    }
}
