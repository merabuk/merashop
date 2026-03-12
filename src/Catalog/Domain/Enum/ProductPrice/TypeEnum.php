<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\ProductPrice;

use App\Shared\Domain\Enum\StringEnumTrait;

enum TypeEnum: string
{
    use StringEnumTrait;

    case Regular = 'regular';
    case Sale = 'sale';
    case Cost = 'cost';
}
