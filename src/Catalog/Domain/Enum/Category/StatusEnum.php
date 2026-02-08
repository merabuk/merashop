<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Category;

enum StatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
