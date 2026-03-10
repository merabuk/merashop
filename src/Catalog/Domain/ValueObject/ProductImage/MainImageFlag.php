<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductImage;

use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

class MainImageFlag implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const bool DEFAULT_VALUE = false;

    public function __construct(
        private readonly bool $value,
    ) {
    }

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_VALUE);
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

    protected function getPrimitiveValue(): bool
    {
        return $this->value;
    }
}
