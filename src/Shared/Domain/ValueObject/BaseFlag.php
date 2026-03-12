<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

readonly class BaseFlag implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public function __construct(
        private bool $value,
    ) {
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function isTrue(): bool
    {
        return true === $this->value;
    }

    public function isFalse(): bool
    {
        return false === $this->value;
    }

    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }

    protected function getPrimitiveValue(): bool
    {
        return $this->value;
    }
}
