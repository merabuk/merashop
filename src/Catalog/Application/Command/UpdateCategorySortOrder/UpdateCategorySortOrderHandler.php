<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategorySortOrder;

use App\Catalog\Application\Exception\Category\UpdateCategoryException;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\CategorySortOrderOutOfRangeException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UpdateCategorySortOrderHandler implements CommandHandlerInterface
{
    public function __construct(
        private CategoryReadRepositoryInterface $readRepository,
        private CategoryWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CategoryNotFoundException
     * @throws CategorySortOrderOutOfRangeException
     * @throws ConcurrencyException
     * @throws UpdateCategoryException
     */
    public function __invoke(UpdateCategorySortOrderCommand $command): void
    {
        try {
            $category = $this->readRepository->getById(Id::fromInt($command->id));

            if ($category->getVersion()->value() !== $command->version) {
                throw new ConcurrencyException();
            }

            $maxOrder = $this->readRepository->getMaxSortOrder($category->getParentId());
            $newSort = SortOrder::fromInt($command->sortOrder);

            if ($newSort->greaterThan($maxOrder)) {
                throw new CategorySortOrderOutOfRangeException();
            }

            $category->updateSortOrder(sortOrder: $newSort);

            $this->writeRepository->save($category);
        } catch (CategoryNotFoundException|CategorySortOrderOutOfRangeException|ConcurrencyException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new UpdateCategoryException(message: 'Error while updating category', previous: $e);
        }
    }
}
