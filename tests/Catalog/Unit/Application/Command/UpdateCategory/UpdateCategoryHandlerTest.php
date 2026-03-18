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
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\Category\CategoryManagerInterface;
use App\Catalog\Domain\Service\Category\CategoryValidatorInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use App\Tests\Catalog\Support\CategoryMother;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final class UpdateCategoryHandlerTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $readRepository;
    private CategoryWriteRepositoryInterface&MockObject $writeRepository;
    private CategoryValidatorInterface&MockObject $categoryValidator;
    private CategoryManagerInterface&MockObject $categoryManager;
    private MessageBusInterface&MockObject $eventBus;

    public function setUp(): void
    {
        $this->readRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->writeRepository = $this->createMock(CategoryWriteRepositoryInterface::class);
        $this->categoryValidator = $this->createMock(CategoryValidatorInterface::class);
        $this->categoryManager = $this->createMock(CategoryManagerInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
    }

    public function testHandleSuccessWithParent(): void
    {
        $oldPath = '/old-parent/category';
        $expectedNewPath = '/new-parent/new-slug';
        $category = CategoryMother::createWithData(
            parentId: 456,
            path: $oldPath,
            slug: 'category',
            id: 123
        );
        $command = $this->fillAndGetCommand(category: $category, slug: 'new-slug', parentId: 789);

        $this->expectCategoryFound($category, $command->id);
        $this->givenSlugIsAvailable($category, $command->version, $command->slug);
        $this->expectCategoryManagerUpdate($category, $command);
        $this->expectSaveCategory($command, $expectedNewPath);
        $this->expectEventDispatched($oldPath, $expectedNewPath);

        $this->createHandler()($command);
    }

    public function testHandleSuccessWithoutParent(): void
    {
        $oldPath = '/old-parent/category';
        $expectedNewPath = '/new-slug';
        $category = CategoryMother::createWithData(
            parentId: 456,
            path: $oldPath,
            slug: 'category',
            id: 123
        );
        $command = $this->fillAndGetCommand(category: $category, slug: 'new-slug');

        $this->expectCategoryFound($category, $command->id);
        $this->givenSlugIsAvailable($category, $command->version, $command->slug);
        $this->expectCategoryManagerUpdate($category, $command);
        $this->expectSaveCategory($command, $expectedNewPath);
        $this->expectEventDispatched($oldPath, $expectedNewPath);

        $this->createHandler()($command);
    }

    public function testHandleSuccessWithoutMoving(): void
    {
        $category = CategoryMother::createWithData(id: 123);
        $command = $this->fillAndGetCommand(category: $category);

        $this->expectCategoryFound($category, $command->id);
        $this->givenSlugIsAvailable($category, $command->version, $command->slug);
        $this->expectCategoryManagerUpdate($category, $command, false);
        $this->expectSaveCategory($command, $category->getPath()->value());
        $this->eventBusNeverCalled();

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfCategoryDoesNotExist(): void
    {
        $category = CategoryMother::createWithData(id: 123);
        $command = $this->fillAndGetCommand(category: $category);

        $this->expectCategoryNotFound($category->getId());
        $this->categoryValidatorNeverCalled();
        $this->categoryManagerNeverCalled();
        $this->saveCategoryNeverCalled();
        $this->eventBusNeverCalled();

        $this->expectException(CategoryNotFoundException::class);

        $this->createHandler()($command);
    }

    public function testThrowsConcurrencyExceptionOnVersionMismatch(): void
    {
        $category = CategoryMother::createWithData(version: 2, id: 123);
        $command = $this->fillAndGetCommand(category: $category, version: 1);

        $this->expectCategoryFound($category, $command->id);
        $this->givenVersionIsInvalid($category, $command->version, $command->slug);
        $this->categoryManagerNeverCalled();
        $this->saveCategoryNeverCalled();
        $this->eventBusNeverCalled();

        $this->expectException(ConcurrencyException::class);

        $this->createHandler()($command);
    }

    public function testThrowsExceptionIfSlugCategoryExists(): void
    {
        $category = CategoryMother::createWithData(slug: 'slug', id: 123);
        $command = $this->fillAndGetCommand(category: $category, slug: 'existing-slug');

        $this->expectCategoryFound($category, $command->id);
        $this->givenSlugIsTaken($category, $command->version, $command->slug);
        $this->categoryManagerNeverCalled();
        $this->saveCategoryNeverCalled();
        $this->eventBusNeverCalled();

        $this->expectException(CategoryAlreadyExistsException::class);

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

        $this->expectCategoryFound($category, $command->id);
        $this->givenSlugIsAvailable($category, $command->version, $command->slug);
        $this->expectCategoryManagerThrowsException($exceptionClass);
        $this->saveCategoryNeverCalled();
        $this->eventBusNeverCalled();

        $this->expectException($exceptionClass);

        $this->createHandler()($command);
    }

    public static function categoryManagerExceptionsProvider(): iterable
    {
        $category = CategoryMother::createWithData(id: 123);

        yield 'category parent not found' => [
            'category' => $category,
            'slug' => 'new-category',
            'parentId' => 456,
            'exceptionClass' => CategoryParentNotFoundException::class,
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
            categoryValidator: $this->categoryValidator,
            categoryManager: $this->categoryManager,
            eventBus: $this->eventBus,
        );
    }

    private function expectCategoryFound(Category $category, int $categoryId): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->equals($category->getId())))
            ->willReturn($category);
    }

    private function expectCategoryNotFound(Id $categoryId): void
    {
        $this->readRepository->expects(self::once())
            ->method('getById')
            ->with(self::callback(fn (Id $id) => $id->equals($categoryId)))
            ->willThrowException(new CategoryNotFoundException());
    }

    private function givenSlugIsAvailable(Category $category, int $version, string $slug): void
    {
        $this->expectValidationCheck($category, $version, $slug);
    }

    private function givenSlugIsTaken(Category $category, int $version, string $slug): void
    {
        $this->expectValidationCheck($category, $version, $slug, new CategoryAlreadyExistsException());
    }

    private function givenVersionIsInvalid(Category $category, int $version, string $slug): void
    {
        $this->expectValidationCheck($category, $version, $slug, new ConcurrencyException());
    }

    private function expectValidationCheck(
        Category $category,
        int $version,
        string $slug,
        ?Throwable $exception = null,
    ): void {
        $invokeContext = $this->categoryValidator->expects(self::once())
            ->method('validateUpdate')
            ->with(
                self::equalTo($category),
                self::equalTo($version),
                self::callback(fn (Slug $s) => $s->value() === $slug)
            );

        if ($exception) {
            $invokeContext->willThrowException($exception);
        }
    }

    private function categoryValidatorNeverCalled(): void
    {
        $this->categoryValidator->expects(self::never())->method('validateUpdate');
    }

    private function expectCategoryManagerUpdate(
        Category $category,
        UpdateCategoryCommand $command,
        bool $expectedResult = true,
    ): void {
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
            ->willReturn($expectedResult);
    }

    private function expectCategoryManagerThrowsException(string $exceptionClass): void
    {
        $this->categoryManager->expects(self::once())
            ->method('updateCategory')
            ->willThrowException(new $exceptionClass());
    }

    private function categoryManagerNeverCalled(): void
    {
        $this->categoryManager->expects(self::never())->method('updateCategory');
    }

    private function expectSaveCategory(UpdateCategoryCommand $command, string $expectedPath): void
    {
        $this->writeRepository->expects(self::once())
            ->method('save')
            ->willReturn(CategoryMother::createWithData(
                parentId: $command->parentId,
                path: $expectedPath,
                slug: $command->slug,
                id: $command->id,
            ));
    }

    private function saveCategoryNeverCalled(): void
    {
        $this->writeRepository->expects(self::never())->method('save');
    }

    private function expectEventDispatched(string $oldPath, string $newPath): void
    {
        $this->eventBus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $event) use ($oldPath, $newPath): bool {
                if (false === $event instanceof CategoryMovedDomainEvent) {
                    return false;
                }

                $oldPathCorrect = $oldPath === $event->oldPath;
                $newPathCorrect = $newPath === $event->newPath;

                return $oldPathCorrect && $newPathCorrect;
            }))
            ->willReturn(new Envelope(new stdClass()));
    }

    private function eventBusNeverCalled(): void
    {
        $this->eventBus->expects(self::never())->method('dispatch');
    }
}
