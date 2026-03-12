<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service\Category;

use App\Catalog\Domain\Exception\Category\CategoryAlreadyExistsException;
use App\Catalog\Domain\ValueObject\Category\Slug;

interface CategoryValidatorInterface
{
    /**
     * @throws CategoryAlreadyExistsException
     */
    public function validateCreation(Slug $slug): void;
}
