<?php

declare(strict_types=1);

use App\Catalog\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::CategoryNotFound->value => 'Category not found',
    ErrorCodeEnum::CategoryParentNotFound->value => 'Category parent not found',
    ErrorCodeEnum::CategoryAlreadyExists->value => 'Category already exists',
    ErrorCodeEnum::CategoryOwnParentConflict->value => 'Category cannot be its own parent',
    ErrorCodeEnum::CategoryOwnDescendantConflict->value => 'Category cannot be its own descendant',
];
