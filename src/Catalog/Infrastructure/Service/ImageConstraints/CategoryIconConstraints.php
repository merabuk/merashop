<?php

namespace App\Catalog\Infrastructure\Service\ImageConstraints;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Shared\Domain\Enum\MimeTypeEnum;
use App\Shared\Domain\Service\Image\ImageConstraintsProviderInterface;
use App\Shared\Domain\ValueObject\File\ImageConstraints;

final readonly class CategoryIconConstraints implements ImageConstraintsProviderInterface
{
    public static function getDefaultIndexName(): string
    {
        return ContextEnum::CategoryIcon->value;
    }

    public function getConstraints(): ImageConstraints
    {
        return new ImageConstraints(
            maxSize: 2 * 1024 * 1024, // 2MB
            allowedMimeTypes: [
                MimeTypeEnum::Jpg->value,
                MimeTypeEnum::Jpeg->value,
                MimeTypeEnum::Png->value,
                MimeTypeEnum::Webp->value,
            ],
            minWidth: 100,
            minHeight: 100,
            maxWidth: 1024,
            maxHeight: 1024
        );
    }
}
