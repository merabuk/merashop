<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum;

enum CatalogEventNameEnum: string
{
    case CategoryMoved = 'catalog.category_moved.v1';
}
