<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Product;

enum StatusEnum: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
