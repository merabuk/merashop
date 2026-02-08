<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Type\Product;

use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

final class StatusType extends AbstractPostgresEnumType
{
    public const string NAME = 'product_status';

    protected function getEnumClass(): string
    {
        return StatusEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
