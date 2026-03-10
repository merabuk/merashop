<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\TemporaryImage;

use App\Catalog\Application\Command\UploadTemporaryImage\UploadTemporaryImageCommand;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Shared\Domain\ValueObject\File\RawFile;

final readonly class UploadTemporaryImageRequest
{
    public function __construct(
        public RawFile $file,
        public ContextEnum $context,
    ) {
    }

    public function toCommand(): UploadTemporaryImageCommand
    {
        return new UploadTemporaryImageCommand($this->file, $this->context);
    }

    /**
     * @return string[]
     */
    public static function getAvailableContexts(): array
    {
        return ContextEnum::getValues();
    }
}
