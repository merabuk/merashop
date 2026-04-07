<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Exception\CatalogDomainException;

final class AttributeStateException extends CatalogDomainException
{
    public static function becauseCanNotSaveAttributeWithUninitializedOptions(): self
    {
        return new self('Cannot save an attribute with uninitialized options collection');
    }

    public static function becauseTypeCanNotBeChanged(string $from, string $to): self
    {
        return new self(sprintf('Attribute type can not be changed from "%s" to "%s"', $from, $to));
    }

    public static function becauseOptionsRequiredForType(string $type): self
    {
        return new self(message: sprintf('Attribute options can not be empty for type "%s"', $type));
    }

    public static function becauseOptionsNotAllowedForType(string $type): self
    {
        return new self(message: sprintf('Attribute options must be empty for type "%s"', $type));
    }

    public function getErrorCode(): string
    {
        return 'ATTRIBUTE_STATE_EXCEPTION';
    }
}
