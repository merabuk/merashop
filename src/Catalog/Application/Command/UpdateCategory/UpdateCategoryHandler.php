<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategory;

use App\Catalog\Application\Exception\Category\UpdateCategoryException;
use App\Catalog\Domain\DTO\CategoryUpdateData;
use App\Catalog\Domain\Event\CategoryMovedDomainEvent;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Exception\Category\CategoryCannotBeParentOfItselfException;
use App\Catalog\Domain\Exception\Category\CategoryChildCanNotBeParentConflictException;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\CategoryParentNotFoundException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\Category\CategoryManagerInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UpdateCategoryHandler implements CommandHandlerInterface
{
    public function __construct(
        private CategoryReadRepositoryInterface $readRepository,
        private CategoryWriteRepositoryInterface $writeRepository,
        private CategoryManagerInterface $categoryManager,
        private MessageBusInterface $eventBus,
    ) {
    }

    /**
     * @throws CategoryAlreadyExistsException
     * @throws CategoryNotFoundException
     * @throws CategoryChildCanNotBeParentConflictException
     * @throws CategoryCannotBeParentOfItselfException
     * @throws CategoryParentNotFoundException
     * @throws ConcurrencyException
     * @throws UpdateCategoryException
     */
    public function __invoke(UpdateCategoryCommand $command): void
    {
        try {
            $category = $this->readRepository->getById(Id::fromInt($command->id));

            if ($category->getVersion()->value() !== $command->version) {
                throw new ConcurrencyException();
            }

            $oldPath = $category->getPath();
            $updateData = new CategoryUpdateData(
                slug: $command->slug,
                parentId: $command->parentId,
                status: $command->status,
                translations: $command->translations,
                adminUlid: $command->adminUlid
            );

            $isMoved = $this->categoryManager->updateCategory(category: $category, data: $updateData);

            $category = $this->writeRepository->save($category);

            if ($isMoved) {
                $this->eventBus->dispatch(new CategoryMovedDomainEvent(
                    oldPath: $oldPath->value(),
                    newPath: $category->getPath()->value()
                ));
            }
        } catch (
            CategoryAlreadyExistsException
            |CategoryNotFoundException
            |CategoryParentNotFoundException
            |CategoryChildCanNotBeParentConflictException
            |CategoryCannotBeParentOfItselfException
            |ConcurrencyException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateCategoryException(message: 'Error while updating category', previous: $e);
        }
    }
}
