<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Application\Command\UpdateCategory;

use App\Catalog\Application\Command\UpdateCategory\UpdateCategoryCommand;
use App\Catalog\Application\Command\UpdateCategory\UpdateCategoryHandler;
use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Event\CategoryMovedDomainEvent;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\CategoryManagerInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateCategoryHandlerTest extends TestCase
{
    private CategoryReadRepositoryInterface $readRepository;
    private CategoryWriteRepositoryInterface $writeRepository;
    private CategoryManagerInterface $categoryManager;
    private MessageBusInterface $eventBus;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(CategoryWriteRepositoryInterface::class);
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
    }

    public function testHandleSuccessWithParent(): void
    {
        $oldParentId = 456;
        $fakeId = 123;
        $category = CategoryMother::createWithData(
            parentId: $oldParentId,
            path: '/old-parent/category',
            slug: 'category',
            id: $fakeId
        );
        $newParentId = 789;

        $command = $this->fillAndGetCommand(
            category: $category,
            slug: 'new-slug',
            parentId: $newParentId,
        );

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->value() === $command->id))
            ->willReturn($category);

        $this->categoryManager->expects(self::once())
            ->method('updateCategory')
            ->with(
                self::equalTo($category),
                self::callback(function (CategoryUpdateData $data) use ($command): bool {
                    $correctSlug = $command->slug == $data->slug;
                    $correctParent = $command->parentId == $data->parentId;

                    return $correctSlug && $correctParent;
                })
            )
            ->willReturn(true);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->willReturn(CategoryMother::createWithData(
                parentId: $newParentId,
                path: '/new-parent/new-slug',
                slug: 'new-slug',
                id: $fakeId,
            ));

        $this->eventBus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $event): bool {
                if (false === $event instanceof CategoryMovedDomainEvent) {
                    return false;
                }

                $oldPathCorrect = '/old-parent/category' === $event->oldPath;
                $newPathCorrect = '/new-parent/new-slug' === $event->newPath;

                return $oldPathCorrect && $newPathCorrect;
            }))
            ->willReturn(new Envelope(new stdClass()));

        $this->createHandler()($command);
    }

    public function testHandleSuccessWithoutParent(): void
    {
        $oldParentId = 456;
        $fakeId = 123;
        $category = CategoryMother::createWithData(
            parentId: $oldParentId,
            path: '/old-parent/category',
            slug: 'category',
            id: $fakeId
        );

        $command = $this->fillAndGetCommand(category: $category, slug: 'new-slug');

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->value() === $command->id))
            ->willReturn($category);

        $this->categoryManager->expects(self::once())
            ->method('updateCategory')
            ->with(
                self::equalTo($category),
                self::callback(function (CategoryUpdateData $data) use ($command): bool {
                    $correctSlug = $command->slug == $data->slug;
                    $correctParent = $command->parentId == $data->parentId;

                    return $correctSlug && $correctParent;
                })
            )
            ->willReturn(true);

        $this->writeRepository->expects(self::once())
            ->method('save')
            ->willReturn(CategoryMother::createWithData(
                path: '/new-slug',
                slug: 'new-slug',
                id: $fakeId,
            ));

        $this->eventBus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $event): bool {
                if (false === $event instanceof CategoryMovedDomainEvent) {
                    return false;
                }

                $oldPathCorrect = '/old-parent/category' === $event->oldPath;
                $newPathCorrect = '/new-slug' === $event->newPath;

                return $oldPathCorrect && $newPathCorrect;
            }))
            ->willReturn(new Envelope(new stdClass()));

        $this->createHandler()($command);
    }

    public function testHandleSuccessWithoutMoving(): void
    {
        $fakeId = 123;
        $category = CategoryMother::createWithData(id: $fakeId);

        $command = $this->fillAndGetCommand(category: $category);

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->value() === $command->id))
            ->willReturn($category);

        $this->categoryManager->expects(self::once())->method('updateCategory')->willReturn(false);

        $this->writeRepository->expects(self::once())->method('save')->willReturn($category);

        $this->eventBus->expects(self::never())->method('dispatch');

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfCategoryDoesNotExist(): void
    {
        $fakeId = 123;
        $category = CategoryMother::createWithData(id: $fakeId);

        $command = $this->fillAndGetCommand(category: $category);

        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->value() === $command->id))
            ->willThrowException(new CategoryNotFoundException());

        $this->categoryManager->expects(self::never())->method('updateCategory');
        $this->writeRepository->expects(self::never())->method('save');
        $this->eventBus->expects(self::never())->method('dispatch');

        $this->expectException(CategoryNotFoundException::class);

        $this->createHandler()($command);
    }

    public function testThrowsConcurrencyExceptionOnVersionMismatch(): void
    {
        $fakeId = 123;
        $category = CategoryMother::createWithData(version: 2, id: $fakeId);

        $command = $this->fillAndGetCommand(category: $category, version: 1);

        $this->readRepository->expects(self::once())->method('getById')->willReturn($category);
        $this->categoryManager->expects(self::never())->method('updateCategory');
        $this->writeRepository->expects(self::never())->method('save');
        $this->eventBus->expects(self::never())->method('dispatch');

        $this->expectException(ConcurrencyException::class);

        $this->createHandler()($command);
    }

    #[DataProvider('categoryManagerExceptionsProvider')]
    public function testThrowsExceptionFromCategoryManager(
        Category $category,
        string $slug,
        ?int $parentId,
        string $exceptionClass,
    ): void {
        $command = $this->fillAndGetCommand(category: $category, slug: $slug, parentId: $parentId);

        $this->readRepository->expects(self::once())->method('getById')->willReturn($category);
        $this->categoryManager->expects(self::once())
            ->method('updateCategory')
            ->willThrowException(new $exceptionClass());
        $this->writeRepository->expects(self::never())->method('save');
        $this->eventBus->expects(self::never())->method('dispatch');

        $this->expectException($exceptionClass);

        $this->createHandler()($command);
    }

    public static function categoryManagerExceptionsProvider(): iterable
    {
        $category = CategoryMother::createWithData(id: 123);

        yield 'slug already exists' => [
            'category' => $category,
            'slug' => 'existing-category',
            'parentId' => null,
            'exceptionClass' => CategoryAlreadyExistsException::class,
        ];
        yield 'try attach parent to itself' => [
            'category' => $category,
            'slug' => $category->getSlug()->value(),
            'parentId' => $category->getId()->value(),
            'exceptionClass' => CategoryCannotBeParentOfItselfException::class,
        ];
        yield 'try make child as parent' => [
            'category' => CategoryMother::createWithData(id: 456),
            'slug' => 'new-category',
            'parentId' => $category->getId()->value(),
            'exceptionClass' => CategoryChildCanNotBeParentConflictException::class,
        ];
    }

    private function fillAndGetCommand(
        Category $category,
        ?string $slug = null,
        ?int $parentId = null,
        ?int $version = null,
    ): UpdateCategoryCommand {
        return new UpdateCategoryCommand(
            id: $category->getId()->value(),
            slug: $slug ?? $category->getSlug()->value(),
            parentId: $parentId,
            status: $category->getStatus()->value()->value,
            translations: $category->getTranslations()->toArray(),
            version: $version ?? $category->getVersion()->value(),
            adminUlid: $category->getCreatedBy()->value()
        );
    }

    private function createHandler(): UpdateCategoryHandler
    {
        return new UpdateCategoryHandler(
            readRepository: $this->readRepository,
            writeRepository: $this->writeRepository,
            categoryManager: $this->categoryManager,
            eventBus: $this->eventBus,
        );
    }
}
