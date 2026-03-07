<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\Service;

use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Service\CategoryManager;
use App\Catalog\Domain\Service\CategoryValidatorInterface;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CategoryManagerTest extends TestCase
{
    private CategoryReadRepositoryInterface $readRepository;
    private CategoryValidatorInterface $validator;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->validator = $this->createMock(CategoryValidatorInterface::class);
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

        $data = new CategoryUpdateData(
            slug: 'new-slug',
            parentId: null,
            status: StatusEnum::Active->value,
            translations: ['en' => ['name' => 'New name', 'description' => 'New description']],
            adminUlid: '01KK4CDAF3V5Y02X33VKGTFWMQ'
        );

        $this->readRepository->expects(self::once())
            ->method('existsBySlug')
            ->with(self::callback(fn (Slug $slug) => $slug->value() === $data->slug))
            ->willReturn(false);

        $this->validator->expects(self::once())
            ->method('canBeAttachedParent')
            ->with(
                self::equalTo($category),
                self::equalTo(null),
            );

        $this->readRepository->expects(self::once())
            ->method('getMaxSortOrder')
            ->with(self::equalTo(null))
            ->willReturn(5);

        $this->createManager()->updateCategory(category: $category, data: $data);

        self::assertSame($data->slug, $category->getSlug()->value());
        self::assertSame($data->parentId, $category->getParentId()?->value());
        self::assertSame('/new-slug', $category->getPath()->value());
        self::assertSame($data->status, $category->getStatus()->value()->value);
        self::assertSame(6, $category->getSortOrder()->value());
        self::assertCount(1, $category->getTranslations());
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
            ulid: '01KK4DQQ4E0JVCWQA5RQW58DFQ',
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

        $data = new CategoryUpdateData(
            slug: 'new-slug',
            parentId: $parent->getId()->value(),
            status: StatusEnum::Active->value,
            translations: ['en' => ['name' => 'New name', 'description' => 'New description']],
            adminUlid: '01KK4CDAF3V5Y02X33VKGTFWMQ'
        );

        $this->readRepository->expects(self::once())
            ->method('existsBySlug')
            ->with(self::callback(fn (Slug $slug) => $slug->value() === $data->slug))
            ->willReturn(false);

        $this->readRepository->expects(self::once())->method('findById')->willReturn($parent);

        $this->validator->expects(self::once())
            ->method('canBeAttachedParent')
            ->with(
                self::equalTo($category),
                self::equalTo($parent),
            );

        $this->readRepository->expects(self::once())
            ->method('getMaxSortOrder')
            ->with(self::equalTo($parent->getId()))
            ->willReturn(5);

        $this->createManager()->updateCategory(category: $category, data: $data);

        self::assertSame($data->slug, $category->getSlug()->value());
        self::assertSame($data->parentId, $category->getParentId()?->value());
        self::assertSame('/parent-slug/new-slug', $category->getPath()->value());
    }

    public function testThrowsExceptionWhenSlugAlreadyExists(): void
    {
        $category = CategoryMother::createWithData();

        $data = new CategoryUpdateData(
            slug: 'existing-slug',
            parentId: null,
            status: StatusEnum::Active->value,
            translations: ['en' => ['name' => 'New name', 'description' => 'New description']],
            adminUlid: CategoryMother::DEFAULT_ADMIN_ULID
        );

        $this->readRepository->expects(self::once())
            ->method('existsBySlug')
            ->with(self::callback(fn (Slug $slug) => $slug->value() === $data->slug))
            ->willReturn(true);

        $this->validator->expects(self::never())->method('canBeAttachedParent');

        $this->expectException(CategoryAlreadyExistsException::class);

        $this->createManager()->updateCategory(category: $category, data: $data);
    }

    /**
     * @param class-string $exceptionClass
     */
    #[DataProvider('validatorErrorProvider')]
    public function testThrowsExceptionWhenValidatorDetectsError(
        int $categoryId,
        string $categoryPath,
        int $parentId,
        string $parentPath,
        string $exceptionClass,
    ): void {
        $category = CategoryMother::createWithData(path: $categoryPath, id: $categoryId);
        $parent = CategoryMother::createWithData(path: $parentPath, id: $parentId);
        $data = new CategoryUpdateData(
            slug: 'slug',
            parentId: $parentId,
            status: StatusEnum::Active->value,
            translations: [],
            adminUlid: CategoryMother::DEFAULT_ADMIN_ULID
        );

        $this->readRepository->expects(self::once())->method('existsBySlug')->willReturn(false);
        $this->readRepository->expects(self::once())->method('findById')->willReturn($parent);

        $this->validator->expects(self::once())
            ->method('canBeAttachedParent')
            ->willThrowException(new $exceptionClass());

        $this->readRepository->expects(self::never())->method('getMaxSortOrder');

        $this->expectException($exceptionClass);

        $this->createManager()->updateCategory($category, $data);
    }

    public static function validatorErrorProvider(): iterable
    {
        yield 'try attach parent to itself' => [
            'categoryId' => 123,
            'categoryPath' => '/category-path',
            'parentId' => 123,
            'parentPath' => '/parent-path',
            'exceptionClass' => CategoryCannotBeParentOfItselfException::class,
        ];
        yield 'try make child as parent' => [
            'categoryId' => 123,
            'categoryPath' => '/category-path',
            'parentId' => 456,
            'parentPath' => '/category-path/parent-path',
            'exceptionClass' => CategoryChildCanNotBeParentConflictException::class,
        ];
    }

    private function createManager(): CategoryManager
    {
        return new CategoryManager(readRepository: $this->readRepository, validator: $this->validator);
    }
}
