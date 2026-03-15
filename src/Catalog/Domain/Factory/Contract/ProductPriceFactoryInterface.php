<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use DateTimeImmutable;

interface ProductPriceFactoryInterface
{
    public function createForTest(
        int $amount,
        CurrencyEnum $currency,
        TypeEnum $type,
        float $taxValue,
        TaxTypeEnum $taxType,
        bool $taxIncluded,
        ?DateTimeImmutable $validFrom,
        ?DateTimeImmutable $validTo,
    ): ProductPrice;
}
