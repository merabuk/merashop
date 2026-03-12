<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductPrice;

use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

final class TaxTypeType extends AbstractPostgresEnumType
{
    public const string NAME = 'product_price_tax_type';

    protected function getEnumClass(): string
    {
        return TaxTypeEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
