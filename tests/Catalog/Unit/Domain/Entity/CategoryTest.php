<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Entity;

use App\Catalog\Domain\DTO\CategoryStructureResult;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Tests\Catalog\Support\CategoryMother;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class CategoryTest extends BaseUnitTest
{
    use ValueObjectAssertionTrait;

    #[DataProvider('createData')]
    public function testItCreatesValidCategory(
        Ulid $ulid,
        ?Id $parentId,
        Path $path,
        Slug $slug,
        SortOrder $sortOrder,
        Status $status,
        Translations $translations,
        AdminUlid $createdBy,
    ): void {
        $category = Category::create(
            ulid: $ulid,
            parentId: $parentId,
            path: $path,
            slug: $slug,
            sortOrder: $sortOrder,
            status: $status,
            translations: $translations,
            createdBy: $createdBy,
        );

        self::assertNull($category->getId());
        self::assertTrue($category->getUlid()->equals($ulid));
        $this->assertVoEqualsOrNull($parentId, $category->getParentId());
        self::assertTrue($category->getPath()->equals($path));
        self::assertTrue($category->getSlug()->equals($slug));
        self::assertTrue($category->getSortOrder()->equals($sortOrder));
        self::assertTrue($category->getStatus()->equals($status));
        self::assertCount($translations->count(), $category->getTranslations());
        foreach ($translations as $locale => $translation) {
            $actualTranslation = $category->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->name, $actualTranslation->name);
            self::assertSame($translation->description, $actualTranslation->description);
        }
        self::assertSame(1, $category->getVersion()->value());
        self::assertTrue($category->getCreatedBy()->equals($createdBy));
        self::assertNull($category->getUpdatedBy());
    }

    public static function createData(): iterable
    {
        $ulid = Ulid::fromString(CategoryMother::DEFAULT_ULID);
        $slug = Slug::fromString('category-slug');
        $sortOrder = SortOrder::fromInt(1);
        $status = Status::active();
        $translations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString(CategoryMother::DEFAULT_ADMIN_ULID);

        yield 'without parent' => [
            'ulid' => $ulid,
            'parentId' => null,
            'path' => Path::fromString('/'.$slug->value()),
            'slug' => $slug,
            'sortOrder' => $sortOrder,
            'status' => $status,
            'translations' => $translations,
            'createdBy' => $adminUlid,
        ];
        yield 'with parent' => [
            'ulid' => $ulid,
            'parentId' => Id::fromInt(123),
            'path' => Path::fromString('/parent-path/'.$slug->value()),
            'slug' => $slug,
            'sortOrder' => $sortOrder,
            'status' => $status,
            'translations' => $translations,
            'createdBy' => $adminUlid,
        ];
    }

    #[DataProvider('updateData')]
    public function testItUpdateChangesState(
        ?Slug $newSlug,
        ?CategoryStructureResult $structure,
    ): void {
        $category = CategoryMother::createWithData(
            parentId: 456,
            sortOrder: 1,
            id: 123
        );

        $oldSlug = $category->getSlug();
        $oldPath = $category->getPath();
        $oldSortOrder = $category->getSortOrder();
        $oldParentId = $category->getParentId();

        $status = Status::active();
        $translations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString('01KHVRCC1Z9S7G603HEPK9MGEZ');

        $category->update(
            status: $status,
            translations: $translations,
            updatedBy: $adminUlid,
            newSlug: $newSlug,
            structure: $structure,
        );

        self::assertTrue($category->getStatus()->equals($status));
        self::assertCount($translations->count(), $category->getTranslations());
        foreach ($translations as $locale => $translation) {
            $actualTranslation = $category->getTranslations()->get($locale);
            self::assertNotNull($actualTranslation);
            self::assertSame($translation->name, $actualTranslation->name);
            self::assertSame($translation->description, $actualTranslation->description);
        }
        self::assertTrue($category->getUpdatedBy()->equals($adminUlid));

        if (null !== $newSlug && null !== $structure) {
            self::assertTrue($category->getSlug()->equals($newSlug));
            self::assertTrue($category->getPath()->equals(Path::fromString($structure->path->value())));
            self::assertTrue($category->getSortOrder()->equals($structure->sortOrder));
            $this->assertVoEqualsOrNull($structure->parentId, $category->getParentId());
        } else {
            self::assertTrue($category->getSlug()->equals($oldSlug));
            self::assertTrue($category->getPath()->equals($oldPath));
            self::assertTrue($category->getSortOrder()->equals($oldSortOrder));
            self::assertTrue($category->getParentId()->equals($oldParentId));
        }
    }

    public static function updateData(): iterable
    {
        yield 'when slug and structure are null' => [
            'newSlug' => null,
            'structure' => null,
        ];
        yield 'when slug is null and structure is not null' => [
            'newSlug' => null,
            'structure' => self::makeCategoryStructureResult(
                newPath: '/new-path',
                newSortOrder: 10,
                newParentId: null,
            ),
        ];
        yield 'when structure is null and slug is not null' => [
            'newSlug' => Slug::fromString('new-slug'),
            'structure' => null,
        ];
        yield 'when slug and structure are not null (parent id null)' => [
            'newSlug' => Slug::fromString('new-slug'),
            'structure' => self::makeCategoryStructureResult(
                newPath: '/new-path',
                newSortOrder: 10,
                newParentId: null,
            ),
        ];
        yield 'when slug and structure are not null (parent id not null)' => [
            'newSlug' => Slug::fromString('new-slug'),
            'structure' => self::makeCategoryStructureResult(
                newPath: '/new-parent/new-path',
                newSortOrder: 10,
                newParentId: 456,
            ),
        ];
    }

    public function testThrowsExceptionWhenTryChangeParentToItSelf(): void
    {
        $category = CategoryMother::createWithData(id: 123);

        $status = Status::active();
        $translations = Translations::fromArray(self::getValidTranslations());
        $adminUlid = AdminUlid::fromString('01KHVRCC1Z9S7G603HEPK9MGEZ');

        $newSlug = Slug::fromString('new-slug');
        $structure = self::makeCategoryStructureResult(
            newPath: '/new-parent/new-path',
            newSortOrder: 10,
            newParentId: $category->getId()->value(),
        );

        $this->expectException(CategoryCannotBeParentOfItselfException::class);

        $category->update(
            status: $status,
            translations: $translations,
            updatedBy: $adminUlid,
            newSlug: $newSlug,
            structure: $structure,
        );
    }

    #[DataProvider('slugData')]
    public function testIsSlugDifferent(
        Category $category,
        Slug $slug,
        bool $expected,
    ): void {
        self::assertSame($expected, $category->isSlugDifferent($slug));
    }

    public static function slugData(): iterable
    {
        yield 'old and new are the same' => [
            'category' => CategoryMother::createWithData(slug: 'test-slug'),
            'slug' => Slug::fromString('test-slug'),
            'expected' => false,
        ];
        yield 'old and new are different' => [
            'category' => CategoryMother::createWithData(slug: 'test-slug'),
            'slug' => Slug::fromString('different-slug'),
            'expected' => true,
        ];
    }

    #[DataProvider('parentIdData')]
    public function testIsParentDifferent(
        Category $category,
        ?Id $parentId,
        bool $expected,
    ): void {
        self::assertSame($expected, $category->isParentDifferent($parentId));
    }

    public static function parentIdData(): iterable
    {
        yield 'old and new are null' => [
            'category' => CategoryMother::createWithData(),
            'parentId' => null,
            'expected' => false,
        ];
        yield 'old and new are the same' => [
            'category' => CategoryMother::createWithData(parentId: 123),
            'parentId' => Id::fromInt(123),
            'expected' => false,
        ];
        yield 'old null and new is not' => [
            'category' => CategoryMother::createWithData(),
            'parentId' => Id::fromInt(123),
            'expected' => true,
        ];
        yield 'old is not null and new is' => [
            'category' => CategoryMother::createWithData(parentId: 123),
            'parentId' => null,
            'expected' => true,
        ];
    }

    #[DataProvider('potentialParentData')]
    public function testCanBeAttachedTo(
        Category $category,
        ?Category $potentialParent,
        ?string $expectedErrorClass,
    ): void {
        if ($expectedErrorClass) {
            $this->expectException($expectedErrorClass);
        } else {
            self::expectNotToPerformAssertions();
        }

        $category->canBeAttachedTo($potentialParent);
    }

    public static function potentialParentData(): iterable
    {
        yield 'parent is null' => [
            'category' => CategoryMother::createWithData(),
            'potentialParent' => null,
            'expectedErrorClass' => null,
        ];
        yield 'parent is the same' => [
            'category' => CategoryMother::createWithData(id: 123),
            'potentialParent' => CategoryMother::createWithData(id: 123),
            'expectedErrorClass' => CategoryCannotBeParentOfItselfException::class,
        ];
        yield 'parent is child' => [
            'category' => CategoryMother::createWithData(
                path: '/parent-path',
                id: 123
            ),
            'potentialParent' => CategoryMother::createWithData(
                parentId: 123,
                path: '/parent-path/child-path',
                id: 456
            ),
            'expectedErrorClass' => CategoryChildCanNotBeParentConflictException::class,
        ];
    }

    private static function makeCategoryStructureResult(
        string $newPath,
        int $newSortOrder,
        ?int $newParentId,
    ): CategoryStructureResult {
        return new CategoryStructureResult(
            path: Path::fromString($newPath),
            sortOrder: SortOrder::fromInt($newSortOrder),
            parentId: $newParentId ? Id::fromInt($newParentId) : null,
        );
    }

    private static function getValidTranslations(): array
    {
        return [
            'en' => [
                'name' => 'Test name',
                'description' => 'Test description',
            ],
            'uk' => [
                'name' => 'Тестове ім\'я',
                'description' => 'Тестова опис',
            ],
        ];
    }
}
