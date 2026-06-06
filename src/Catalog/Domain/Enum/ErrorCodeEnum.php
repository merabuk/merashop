<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enum;

enum ErrorCodeEnum: string
{
    case CatalogDomainError = 'catalog_domain_error';
    case AttributeNotFound = 'attribute_notFound';
    case AttributeAlreadyExists = 'attribute_alreadyExists';
    case AttributeTypeCanNotBeChanged = 'attributeType_canNotBeChanged';
    case OneOfAttributesNotFound = 'oneOfAttributes_notFound';
    case AttributeOptionNotFound = 'attributeOption_notFound';
    case OneOfAttributeOptionsNotFound = 'oneOfAttributeOptions_notFound';
    case CategoryNotFound = 'category_notFound';
    case CategoryAlreadyExists = 'category_alreadyExists';
    case CategorySortOrderOutOfRange = 'categorySortOrder_outOfRange';
    case OneOfCategoriesNotFound = 'oneOfCategories_notFound';
    case CategoryParentNotFound = 'categoryParent_notFound';
    case CategoryCannotBeParentOfItselfConflict = 'categoryCanNotBeParentOfItself_conflict';
    case CategoryChildCanNotBeParentConflictException = 'categoryMoveToChild_conflict';
    case ProductNotFound = 'product_notFound';
    case ProductAlreadyExists = 'product_alreadyExists';
    case ProductImagesCanNotBeEmpty = 'productImages_canNotBeEmpty';
    case OneOfTemporaryImagesNotFound = 'oneOfTemporaryImages_notFound';
}
