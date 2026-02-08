<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Type\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

final class TypeType extends AbstractPostgresEnumType
{
    public const string NAME = 'attribute_type';

    protected function getEnumClass(): string
    {
        return TypeEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
