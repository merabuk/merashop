<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Attribute;

use App\Shared\Domain\Enum\StringEnumTrait;

enum TypeEnum: string
{
    use StringEnumTrait;

    case String = 'string';
    case Int = 'int';
    case Boolean = 'boolean';
    case Select = 'select';
}
