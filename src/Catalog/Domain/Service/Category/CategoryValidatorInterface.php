<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Shared\Domain\Exception\Entity\ConcurrencyException;

interface CategoryValidatorInterface
{
    /**
     * @throws CategoryAlreadyExistsException
     */
    public function validateCreation(Slug $slug): void;

    /**
     * @throws ConcurrencyException
     * @throws CategoryAlreadyExistsException
     */
    public function validateUpdate(Category $category, int $version, Slug $newSlug): void;
}
