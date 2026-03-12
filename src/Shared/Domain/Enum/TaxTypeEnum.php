<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

enum TaxTypeEnum: string
{
    use StringEnumTrait;

    case Percentage = 'percentage';
    case Fixed = 'fixed';
}
