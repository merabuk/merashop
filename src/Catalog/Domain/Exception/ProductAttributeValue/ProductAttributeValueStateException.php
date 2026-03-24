<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\ProductAttributeValue;

use App\Catalog\Domain\Exception\CatalogDomainException;

final class ProductAttributeValueStateException extends CatalogDomainException
{
    /**
     * @param string[] $fields
     */
    public static function becauseAllFieldsAreNull(array $fields): self
    {
        return new self(sprintf('One of the following fields must be not null: %s', implode(', ', $fields)));
    }

    /**
     * @param string[] $fields
     */
    public static function becauseAllFieldsAreNotNull(array $fields): self
    {
        return new self(sprintf('One of the following fields must be null: %s', implode(', ', $fields)));
    }

    /**
     * @param string[] $fields
     */
    public static function becauseOneFieldIsNull(array $fields): self
    {
        return new self(sprintf('Fields "%s" must be not null', implode('", "', $fields)));
    }

    public function getErrorCode(): string
    {
        return 'PRODUCT_ATTRIBUTE_VALUE_STATE_EXCEPTION';
    }
}
