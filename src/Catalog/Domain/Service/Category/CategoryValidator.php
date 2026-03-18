<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;

final readonly class CategoryValidator implements CategoryValidatorInterface
{
    public function __construct(
        private CategoryReadRepositoryInterface $readRepository,
    ) {
    }

    /**
     * @throws CategoryAlreadyExistsException
     */
    public function validateCreation(Slug $slug): void
    {
        if ($this->readRepository->existsBySlug($slug)) {
            throw CategoryAlreadyExistsException::becauseSlugAlreadyExists($slug->value());
        }
    }

    /**
     * @throws ConcurrencyException
     * @throws CategoryAlreadyExistsException
     */
    public function validateUpdate(
        Category $category,
        int $version,
        Slug $newSlug,
    ): void {
        if ($category->getVersion()->value() !== $version) {
            throw new ConcurrencyException();
        }

        if ($category->isSlugDifferent($newSlug) && $this->readRepository->existsBySlug($newSlug, $category->getId())) {
            throw CategoryAlreadyExistsException::becauseSlugAlreadyExists($newSlug->value());
        }
    }
}
