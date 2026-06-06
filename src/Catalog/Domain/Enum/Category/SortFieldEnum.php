<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Category;

use App\Shared\Domain\Enum\StringEnumTrait;

enum SortFieldEnum: string
{
    use StringEnumTrait;

    case Name = 'name';
    case Slug = 'slug';
}
