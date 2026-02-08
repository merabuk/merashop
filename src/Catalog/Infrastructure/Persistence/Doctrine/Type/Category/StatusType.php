<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Type\Category;

use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

final class StatusType extends AbstractPostgresEnumType
{
    public const string NAME = 'category_status';

    protected function getEnumClass(): string
    {
        return StatusEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
