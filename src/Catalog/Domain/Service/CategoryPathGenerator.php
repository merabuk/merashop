<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;

class CategoryPathGenerator
{
    public const string ROOT_PATH = 'catalog';
    public const string PATH_SEPARATOR = '/';

    /**
     * @throws InvalidCategoryPathException
     */
    public function generate(Slug $slug, ?Path $parentPath = null): Path
    {
        $newPath = implode(self::PATH_SEPARATOR, array_filter([$parentPath?->value(), $slug->value()]));

        return Path::fromString($newPath);
    }
}
