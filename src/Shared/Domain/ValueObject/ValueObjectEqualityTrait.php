<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

trait ValueObjectEqualityTrait
{
    abstract protected function getPrimitiveValue(): string|int|float|bool;

    public function equals(object $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->getPrimitiveValue() === $other->getPrimitiveValue();
    }

    public function __toString(): string
    {
        return (string) $this->getPrimitiveValue();
    }
}
