<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\Repository\CategoryReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Category\Slug;

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
}
