<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateCategory;

use App\Catalog\Application\Exception\Category\CreateCategoryException;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\InvalidCatalogValueObjectException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\CategoryPathGenerator;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\SortOrder;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateCategoryHandler implements CommandHandlerInterface
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
        private CategoryPathGenerator $pathGenerator,
        private CategoryReadRepositoryInterface $readRepository,
        private CategoryWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CreateCategoryException
     */
    public function __invoke(CreateCategoryCommand $command): int
    {
        try {
            $parentId = $command->parentId ? Id::fromInt($command->parentId) : null;
            $parent = $parentId ? $this->readRepository->findById($parentId) : null;

            $ulid = $this->ulidGenerator->next();
            $slug = Slug::fromString($command->slug);
            $path = $this->pathGenerator->generate($slug, $parent?->getPath());

            $maxSortOrder = $this->readRepository->getMaxSortOrder($parentId);

            $category = Category::create(
                ulid: Ulid::fromString($ulid),
                parentId: $parentId,
                path: $path,
                slug: $slug,
                sortOrder: SortOrder::fromInt($maxSortOrder)->next(),
                status: Status::fromString($command->status),
                translations: Translations::fromArray($command->translations),
            );

            $category = $this->writeRepository->save($category);

            return $category->getId()->value();
        } catch (InvalidCatalogValueObjectException|InvalidLocaleException $e) {
            throw new CreateCategoryException(message: 'Error during creating category', previous: $e);
        }
    }
}
