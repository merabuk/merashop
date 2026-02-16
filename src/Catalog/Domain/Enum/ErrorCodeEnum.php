<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum;

enum ErrorCodeEnum: string
{
    case CatalogDomainError = 'CATALOG_DOMAIN_ERROR';
    case AttributeNotFound = 'ATTRIBUTE_NOT_FOUND';
    case AttributeAlreadyExists = 'ATTRIBUTE_ALREADY_EXISTS';
    case OneOfAttributesNotFound = 'ONE_OF_ATTRIBUTES_NOT_FOUND';
    case CategoryNotFound = 'CATEGORY_NOT_FOUND';
    case OneOfCategoriesNotFound = 'ONE_OF_CATEGORIES_NOT_FOUND';
    case CategoryParentNotFound = 'CATEGORY_PARENT_NOT_FOUND';
    case CategoryAlreadyExists = 'CATEGORY_ALREADY_EXISTS';
    case CategoryOwnParentConflict = 'CATEGORY_OWN_PARENT_CONFLICT';
    case CategoryOwnDescendantConflict = 'CATEGORY_OWN_DESCENDANTS_CONFLICT';
    case ProductNotFound = 'PRODUCT_NOT_FOUND';
}
