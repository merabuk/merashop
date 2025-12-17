<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

/**
 * @mixin \StringBackedEnum
 */
trait StringEnumTrait
{
    /**
     * @return string[]
     */
    public static function getValues(): array
    {
        return array_column(static::cases(), 'value');
    }
}
