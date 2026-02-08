<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum\Attribute;

enum TypeEnum: string
{
    case String = 'string';
    case Int = 'int';
    case Boolean = 'boolean';
    case Select = 'select';
}
