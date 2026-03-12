<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Utility;

use App\Shared\Domain\Enum\CurrencyEnum;

class CurrencyHelper
{
    public static function formatPrice(int $amount, CurrencyEnum $currency): string
    {
        $currencySymbol = self::formatCurrency($currency);
        $price = self::formatAmount($amount);

        return match ($currency) {
            CurrencyEnum::UAH => sprintf('%s %s', $price, $currencySymbol),
            CurrencyEnum::USD => sprintf('%s%s', $currencySymbol, $price),
        };
    }

    private static function formatAmount(int $amount): string
    {
        return number_format($amount / 100, 2, '.', ' ');
    }

    private static function formatCurrency(CurrencyEnum $currency): string
    {
        return match ($currency) {
            CurrencyEnum::USD => '$',
            CurrencyEnum::UAH => '₴', // 'грн.'
        };
    }
}
