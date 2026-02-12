<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

/**
 * ISO 4217.
 */
enum CurrencyEnum: string
{
    use StringEnumTrait;

    case USD = 'USD';
    case UAH = 'UAH';

    public static function default(): self
    {
        return self::UAH;
    }
}
