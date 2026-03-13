<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enum;

/**
 * ISO 4217.
 */
enum CurrencyEnum: string
{
    use StringEnumTrait;

    case UAH = 'UAH';
    case USD = 'USD';

    public static function default(): self
    {
        return self::UAH;
    }
}
