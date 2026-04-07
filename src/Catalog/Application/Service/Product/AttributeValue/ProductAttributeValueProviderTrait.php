<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Shared\Domain\Exception\InvalidArgumentException;

trait ProductAttributeValueProviderTrait
{
    protected function makeInvalidValueDataException(string $actualClass, string $expectedClass): InvalidArgumentException
    {
        return new InvalidArgumentException(sprintf('Invalid value data type: %s. Expected %s', $actualClass, $expectedClass));
    }

    protected function checkAttributeType(Attribute $attribute): void
    {
        if ($attribute->getType()->value() !== static::getAttributeType()) {
            throw new InvalidArgumentException(sprintf('Attribute type "%s" mismatch. Expected "%s"', $attribute->getType(), static::getAttributeType()->value));
        }
    }

    abstract protected static function getAttributeType(): TypeEnum;
}
