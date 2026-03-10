<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Exception\TemporaryImage\InvalidTemporaryImageUlidException;
use App\Catalog\Domain\Factory\Contract\TemporaryImageFactoryInterface;
use App\Catalog\Domain\ValueObject\TemporaryImage\Context;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;

final readonly class TemporaryImageFactory implements TemporaryImageFactoryInterface
{
    /**
     * @throws InvalidRelativePathException
     * @throws InvalidTemporaryImageUlidException
     */
    public function createForTest(
        string $ulid,
        string $path,
        ContextEnum $context,
    ): TemporaryImage {
        return TemporaryImage::create(
            ulid: Ulid::fromString($ulid),
            path: RelativeFilePath::fromString($path),
            context: Context::fromEnum($context),
        );
    }
}
