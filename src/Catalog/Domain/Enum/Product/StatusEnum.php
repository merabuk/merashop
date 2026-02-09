<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Product;

use App\Shared\Domain\Enum\StringEnumTrait;

enum StatusEnum: string
{
    use StringEnumTrait;

    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
