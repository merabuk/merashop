<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\ValueObject;

final class InvalidAbstractCollectionItemException extends InvalidValueObjectExceptionInterface
{
    public static function becauseItIsNotAValidCollectionItem(string $className, int $index): self
    {
        return new self(sprintf('All items must be of type %s. Invalid item at index %d', $className, $index));
    }
}
