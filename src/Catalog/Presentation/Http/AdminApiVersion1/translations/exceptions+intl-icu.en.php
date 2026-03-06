<?php

declare(strict_types=1);

use App\Catalog\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::AttributeAlreadyExists->value => 'Attribute already exists',
    ErrorCodeEnum::AttributeNotFound->value => 'Attribute not found',
    ErrorCodeEnum::OneOfAttributesNotFound->value => 'One or more attributes not found',
    ErrorCodeEnum::CategoryNotFound->value => 'Category not found',
    ErrorCodeEnum::CategoryAlreadyExists->value => 'Category already exists',
    ErrorCodeEnum::CategorySortOrderOutOfRange->value => 'Category sort order out of range',
    ErrorCodeEnum::OneOfCategoriesNotFound->value => 'One or more categories not found',
    ErrorCodeEnum::CategoryParentNotFound->value => 'Category parent not found',
    ErrorCodeEnum::CategoryCannotBeParentOfItselfConflict->value => 'Category cannot be its own parent',
    ErrorCodeEnum::CategoryMoveToChildConflictException->value => 'Category cannot be its own descendant',
    ErrorCodeEnum::ProductNotFound->value => 'Product not found',
    ErrorCodeEnum::ProductAlreadyExists->value => 'Product already exists',
];
