<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum;

enum CatalogEventNameEnum: string
{
    case CategoryMoved = 'catalog.category_moved.v1';
    case ProductImagesRemoved = 'catalog.product_images_removed.v1';
}
