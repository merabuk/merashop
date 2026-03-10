<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Service\ImageConstraints;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Shared\Domain\Service\ImageConstraintsProviderInterface;
use App\Shared\Domain\ValueObject\ImageConstraints;

final readonly class ProductMainImageConstraints implements ImageConstraintsProviderInterface
{
    public static function getDefaultIndexName(): string
    {
        return ContextEnum::ProductMain->value;
    }

    public function getConstraints(): ImageConstraints
    {
        return new ImageConstraints(
            maxSize: 10 * 1024 * 1024, // 10MB
            allowedMimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            maxWidth: 5000,
            maxHeight: 5000
        );
    }
}
