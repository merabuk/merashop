<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Attribute;

use App\Shared\Domain\Enum\StringEnumTrait;

enum TypeEnum: string
{
    use StringEnumTrait;

    case String = 'string';
    case Text = 'text';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';
    case Select = 'select';
    case MultiSelect = 'multiselect';
    case Color = 'color';
    case Date = 'date';
    case Url = 'url';
    case Dimension = 'dimension';
    case Image = 'image';
}
