<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Shared\Domain\ValueObject\BaseFlag;

final readonly class TaxIncludedFlag extends BaseFlag
{
    public const bool DEFAULT_VALUE = true;
    public const string TRUE_STRING = 'included';
    public const string FALSE_STRING = 'excluded';

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_VALUE);
    }
}
