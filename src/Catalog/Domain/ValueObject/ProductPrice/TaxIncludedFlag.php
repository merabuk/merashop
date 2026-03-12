<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Shared\Domain\ValueObject\BaseFlag;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class TaxIncludedFlag extends BaseFlag
{
    use ValueObjectEqualityTrait;

    public const bool DEFAULT_VALUE = true;

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_VALUE);
    }
}
