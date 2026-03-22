<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Attribute;

use App\Shared\Domain\Enum\StringEnumTrait;

enum TypeEnum: string
{
    use StringEnumTrait;

    case String = 'string';
    case Text = 'text';
    case Int = 'int';
    case Float = 'float';
    case Boolean = 'boolean';
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case Color = 'color';
    case Date = 'date';
    case Url = 'url';
    case Dimension = 'dimension';
    case Image = 'image';
}
