<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductPrice;

use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

final class TypeType extends AbstractPostgresEnumType
{
    public const string NAME = 'product_price_type';

    protected function getEnumClass(): string
    {
        return TypeEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
