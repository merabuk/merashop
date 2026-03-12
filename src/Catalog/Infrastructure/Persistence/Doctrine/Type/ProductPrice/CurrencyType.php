<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductPrice;

use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

final class CurrencyType extends AbstractPostgresEnumType
{
    public const string NAME = 'product_price_currency';

    protected function getEnumClass(): string
    {
        return CurrencyEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
