<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption;

use App\Shared\Domain\ValueObject\BaseFlag;

final readonly class ActiveFlag extends BaseFlag
{
    public static function fromBool(bool $value): self
    {
        return new self($value);
    }

    public static function inactive(): self
    {
        return new self(false);
    }
}
