<?php

declare(strict_types=1);

use App\Catalog\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::CatalogDomainError->value => 'Something went wrong in the catalog domain. Please try again later',
    ErrorCodeEnum::AttributeNotFound->value => 'Attribute not found',
    ErrorCodeEnum::AttributeAlreadyExists->value => 'Attribute with code "{code}" already exists',
    ErrorCodeEnum::AttributeTypeCanNotBeChanged->value => 'Attribute type cannot be changed',
    ErrorCodeEnum::OneOfAttributesNotFound->value => 'One or more attributes not found',
    ErrorCodeEnum::AttributeOptionNotFound->value => 'Attribute option not found',
    ErrorCodeEnum::OneOfAttributeOptionsNotFound->value => 'One or more attribute options not found',
    ErrorCodeEnum::CategoryNotFound->value => 'Category not found',
    ErrorCodeEnum::CategoryAlreadyExists->value => 'Category with slug "{slug}" already exists',
    ErrorCodeEnum::CategorySortOrderOutOfRange->value => 'Category sort order out of range',
    ErrorCodeEnum::OneOfCategoriesNotFound->value => 'One or more categories not found',
    ErrorCodeEnum::CategoryParentNotFound->value => 'Category parent not found',
    ErrorCodeEnum::CategoryCannotBeParentOfItselfConflict->value => 'Category cannot be parent to itself',
    ErrorCodeEnum::CategoryChildCanNotBeParentConflictException->value => 'A child category cannot be set as a parent',
    ErrorCodeEnum::ProductNotFound->value => 'Product not found',
    ErrorCodeEnum::ProductAlreadyExists->value => 'Product with sku "{sku}" already exists',
    ErrorCodeEnum::OneOfTemporaryImagesNotFoundException->value => 'One or more temporary images not found',
];
