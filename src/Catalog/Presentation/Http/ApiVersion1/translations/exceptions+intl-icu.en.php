<?php

declare(strict_types=1);

use App\Catalog\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::AttributeNotFound->value => 'Attribute not found',
    ErrorCodeEnum::OneOfAttributesNotFound->value => 'One or more attributes not found',
    ErrorCodeEnum::CategoryNotFound->value => 'Category not found',
    ErrorCodeEnum::OneOfCategoriesNotFound->value => 'One or more categories not found',
    ErrorCodeEnum::CategoryParentNotFound->value => 'Category parent not found',
    ErrorCodeEnum::CategoryAlreadyExists->value => 'Category already exists',
    ErrorCodeEnum::CategoryOwnParentConflict->value => 'Category cannot be its own parent',
    ErrorCodeEnum::CategoryOwnDescendantConflict->value => 'Category cannot be its own descendant',
];
