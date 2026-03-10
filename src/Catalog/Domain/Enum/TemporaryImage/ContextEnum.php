<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\TemporaryImage;

use App\Shared\Domain\Enum\StringEnumTrait;

enum ContextEnum: string
{
    use StringEnumTrait;

    case ProductMain = 'product_main';
    case CategoryIcon = 'category_icon';
}
