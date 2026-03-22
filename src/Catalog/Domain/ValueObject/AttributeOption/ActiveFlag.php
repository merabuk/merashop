<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption;

use App\Shared\Domain\ValueObject\BaseFlag;

final readonly class ActiveFlag extends BaseFlag
{
    public const bool DEFAULT_VALUE = false;

    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_VALUE);
    }
}
