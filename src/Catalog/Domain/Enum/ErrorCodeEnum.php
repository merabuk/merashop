<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum;

enum ErrorCodeEnum: string
{
    case CatalogDomainError = 'CATALOG_DOMAIN_ERROR';
    case AttributeNotFound = 'ATTRIBUTE_NOT_FOUND';
    case AttributeAlreadyExists = 'ATTRIBUTE_ALREADY_EXISTS';
    case AttributeTypeCanNotBeChanged = 'ATTRIBUTE_TYPE_CANNOT_BE_CHANGED';
    case OneOfAttributesNotFound = 'ONE_OF_ATTRIBUTES_NOT_FOUND';
    case CategoryNotFound = 'CATEGORY_NOT_FOUND';
    case CategoryAlreadyExists = 'CATEGORY_ALREADY_EXISTS';
    case CategorySortOrderOutOfRange = 'CATEGORY_SORT_ORDER_OUT_OF_RANGE';
    case OneOfCategoriesNotFound = 'ONE_OF_CATEGORIES_NOT_FOUND';
    case CategoryParentNotFound = 'CATEGORY_PARENT_NOT_FOUND';
    case CategoryCannotBeParentOfItselfConflict = 'CATEGORY_CANNOT_BE_PARENT_OF_ITSELF_CONFLICT';
    case CategoryChildCanNotBeParentConflictException = 'CATEGORY_MOVE_TO_CHILD_CONFLICT';
    case ProductNotFound = 'PRODUCT_NOT_FOUND';
    case ProductAlreadyExists = 'PRODUCT_ALREADY_EXISTS';
    case OneOfTemporaryImagesNotFoundException = 'ONE_OF_TEMPORARY_IMAGES_NOT_FOUND';
}
