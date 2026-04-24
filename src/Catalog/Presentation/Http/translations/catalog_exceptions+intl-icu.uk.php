<?php

declare(strict_types=1);

use App\Catalog\Domain\Enum\ErrorCodeEnum;

return [
    ErrorCodeEnum::CatalogDomainError->value => 'Виникла помилка в роботі каталогу. Спробуйте пізніше',

    ErrorCodeEnum::AttributeNotFound->value => 'Атрибут за полем {field} із значенням "{value}" не знайдено',
    ErrorCodeEnum::AttributeAlreadyExists->value => 'Атрибут із кодом "{code}" вже існує',
    ErrorCodeEnum::AttributeTypeCanNotBeChanged->value => 'Тип атрибута неможливо змінити після створення',
    ErrorCodeEnum::OneOfAttributesNotFound->value => 'Один або декілька атрибутів не знайдено',

    ErrorCodeEnum::AttributeOptionNotFound->value => 'Варіант атрибута за полем {field} із значенням "{value}" не знайдено',
    ErrorCodeEnum::OneOfAttributeOptionsNotFound->value => 'Один або декілька варіантів атрибутів не знайдено',

    ErrorCodeEnum::CategoryNotFound->value => 'Категорію не знайдено',
    ErrorCodeEnum::CategoryAlreadyExists->value => 'Категорія з посиланням (slug) "{slug}" вже існує',
    ErrorCodeEnum::CategorySortOrderOutOfRange->value => 'Порядок сортування категорії виходить за межі допустимого діапазону',
    ErrorCodeEnum::OneOfCategoriesNotFound->value => 'Одну або декілька категорій не знайдено',
    ErrorCodeEnum::CategoryParentNotFound->value => 'Батьківську категорію не знайдено',
    ErrorCodeEnum::CategoryCannotBeParentOfItselfConflict->value => 'Категорія не може бути батьківською для самої себе',
    ErrorCodeEnum::CategoryChildCanNotBeParentConflictException->value => 'Підкатегорія не може бути призначена батьківською для основної категорії',

    ErrorCodeEnum::ProductNotFound->value => 'Товар за полем {field} із значенням "{value}" не знайдено',
    ErrorCodeEnum::ProductAlreadyExists->value => 'Товар за полем {field} із значенням "{value}" вже існує',
    ErrorCodeEnum::ProductImagesCanNotBeEmpty->value => 'Активний товар повинен мати хоча б одне зображення',

    ErrorCodeEnum::OneOfTemporaryImagesNotFound->value => 'Одне або декілька тимчасових зображень не знайдено',
];
