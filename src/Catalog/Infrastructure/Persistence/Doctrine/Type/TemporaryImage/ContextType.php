<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Type\TemporaryImage;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Type\AbstractPostgresEnumType;

final class ContextType extends AbstractPostgresEnumType
{
    public const string NAME = 'temporary_image_context';

    protected function getEnumClass(): string
    {
        return ContextEnum::class;
    }

    public function getEnumName(): string
    {
        return self::NAME;
    }
}
