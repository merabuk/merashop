<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Attribute;

use App\Shared\Domain\Enum\StringEnumTrait;

enum SortFieldEnum: string
{
    use StringEnumTrait;

    case Name = 'name';
    case Code = 'code';
    case Type = 'type';
}
