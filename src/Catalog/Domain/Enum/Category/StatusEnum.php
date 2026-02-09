<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Category;

use App\Shared\Domain\Enum\StringEnumTrait;

enum StatusEnum: string
{
    use StringEnumTrait;

    case Active = 'active';
    case Inactive = 'inactive';
}
