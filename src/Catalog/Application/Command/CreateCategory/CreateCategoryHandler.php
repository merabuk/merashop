<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateCategory;

use App\Catalog\Application\Exception\Category\CreateCategoryException;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Repository\CategoryWriteRepositoryInterface;
use App\Catalog\Domain\Service\Category\CategoryStructureServiceInterface;
use App\Catalog\Domain\Service\Category\CategoryValidatorInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\Category\Id;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\Status;
use App\Catalog\Domain\ValueObject\Category\Translations;
use App\Catalog\Domain\ValueObject\Category\Ulid;
use App\Shared\Application\Bus\BusNameEnum;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler(bus: BusNameEnum::Command->value)]
readonly class CreateCategoryHandler implements CommandHandlerInterface
{
    public function __construct(
        private UlidGeneratorInterface $ulidGenerator,
        private CategoryValidatorInterface $categoryValidator,
        private CategoryStructureServiceInterface $categoryStructureService,
        private CategoryWriteRepositoryInterface $writeRepository,
    ) {
    }

    /**
     * @throws CategoryAlreadyExistsException
     * @throws CreateCategoryException
     */
    public function __invoke(CreateCategoryCommand $command): int
    {
        try {
            $slug = Slug::fromString($command->slug);

            $this->categoryValidator->validateCreation($slug);

            $ulid = $this->ulidGenerator->next();
            $parentId = $command->parentId ? Id::fromInt($command->parentId) : null;
            $structure = $this->categoryStructureService->prepareStructure($slug, $parentId);

            $category = Category::create(
                ulid: Ulid::fromString($ulid),
                parentId: $structure->parentId,
                path: $structure->path,
                slug: $slug,
                sortOrder: $structure->sortOrder,
                status: Status::fromString($command->status),
                translations: Translations::fromArray($command->translations),
                createdBy: AdminUlid::fromString($command->adminUlid),
            );

            $category = $this->writeRepository->save($category);

            return $category->getId()->value();
        } catch (CategoryAlreadyExistsException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new CreateCategoryException(message: 'Error during creating category', previous: $e);
        }
    }
}
