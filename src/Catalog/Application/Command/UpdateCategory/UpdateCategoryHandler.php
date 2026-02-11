<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategory;

use App\Catalog\Application\Exception\Category\UpdateCategoryException;
use App\Catalog\Domain\Event\CategoryMovedDomainEvent;
use App\Catalog\Domain\Exception\Category\CategoryNotFoundException;
use App\Catalog\Domain\Exception\Category\CategoryOwnDescendantConflictException;
use App\Catalog\Domain\Exception\Category\CategoryOwnParentConflictException;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\CategoryPathGenerator;
use App\Catalog\Domain\Service\CategoryValidator;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class UpdateCategoryHandler implements CommandHandlerInterface
{
    public function __construct(
        private CategoryValidator $validator,
        private CategoryReadRepositoryInterface $readRepository,
        private CategoryWriteRepositoryInterface $writeRepository,
        private CategoryPathGenerator $pathGenerator,
        private MessageBusInterface $eventBus,
    ) {
    }

    /**
     * @throws CategoryNotFoundException
     * @throws CategoryOwnDescendantConflictException
     * @throws CategoryOwnParentConflictException
     * @throws UpdateCategoryException
     */
    public function __invoke(UpdateCategoryCommand $command): void
    {
        try {
            $category = $this->readRepository->getById(Id::fromInt($command->id));

            $newParentId = $command->parentId ? Id::fromInt($command->parentId) : null;
            $parent = $newParentId ? $this->readRepository->findById($newParentId) : null;

            $this->validator->canBeAttachedParent($category, $parent);

            $newSlug = Slug::fromString($command->slug);
            $newPath = $oldPath = $category->getPath();

            $slugHasChanged = !$category->getSlug()->equals($newSlug);
            $parentHasChanged = (null === $parent && null !== $category->getParentId())
                || !$category->getParentId()?->equals($parent->getId());
            $needChangePath = $slugHasChanged || $parentHasChanged;

            if ($needChangePath) {
                $newPath = $this->pathGenerator->generate($newSlug, $parent?->getPath());
            }

            if ($parentHasChanged) {
                $newSort = SortOrder::fromInt($this->readRepository->getMaxSortOrder($parent?->getId()))->next();
            } else {
                $newSort = SortOrder::fromInt($command->sortOrder); // TODO: validate sort order available order range?
            }

            $category->update(
                parentId: $newParentId,
                path: $newPath,
                slug: $newSlug,
                sortOrder: $newSort,
                status: Status::fromString($command->status),
                translations: Translations::fromArray($command->translations)
            );

            $this->writeRepository->save($category);

            if ($needChangePath) {
                $this->eventBus->dispatch(new CategoryMovedDomainEvent(
                    oldPath: $oldPath->value(),
                    newPath: $newPath->value(),
                ));
            }
        } catch (InvalidCatalogValueObjectException|InvalidLocaleException|ExceptionInterface $e) {
            throw new UpdateCategoryException(message: 'Error while updating category', previous: $e);
        }
    }
}
