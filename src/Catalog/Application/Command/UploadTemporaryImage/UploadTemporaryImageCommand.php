<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UploadTemporaryImage;

use App\Catalog\Domain\Enum\ImageContextEnum;
use App\Shared\Application\Command\CommandInterface;
use App\Shared\Domain\ValueObject\RawFile;

final readonly class UploadTemporaryImageCommand implements CommandInterface
{
    public function __construct(
        public RawFile $file,
        public ImageContextEnum $context,
    ) {
    }
}
