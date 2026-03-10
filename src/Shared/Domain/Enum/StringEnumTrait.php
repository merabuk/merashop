<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

use StringBackedEnum;

/**
 * @mixin StringBackedEnum
 */
trait StringEnumTrait
{
    /**
     * @param ?static[] $cases
     *
     * @return string[]
     */
    public static function getValues(?array $cases = null): array
    {
        return array_column($cases ?? static::cases(), 'value');
    }
}
